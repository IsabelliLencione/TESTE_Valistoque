#include <SoftwareSerial.h>
#include "HX711.h"

// ==================== BALANÇAS ====================

const byte TOTAL_BALANCAS = 3;

// Cada coluna corresponde à mesma prateleira.
//                                   P1      P2      P3
const byte PRATELEIRA[]           = {1,      2,      3};
const byte PINO_DT[]              = {4,      6,      8};
const byte PINO_SCK[]             = {5,      7,      9};
const float FATOR_CALIBRACAO[]    = {426.35, 426.80, 427.05};

HX711 balancas[TOTAL_BALANCAS];
bool temTara[TOTAL_BALANCAS] = {false, false, false};

// ==================== ESP8266 ====================

// TX do adaptador → D3 do Uno
// RX do adaptador → D2 do Uno
SoftwareSerial esp8266(3, 2);

const char REDE[] = "ana_pc";
const char SENHA[] = "12345678";

const char SERVIDOR[] = "10.136.86.219";
const unsigned int PORTA = 80;
const char CAMINHO[] =
  "/TCC-02/TESTE_Valistoque/php/leitura_prateleira.php";

const unsigned long INTERVALO = 10000UL;
unsigned long fimDoCiclo = 0;
bool primeiroCiclo = true;
bool wifiConectado = false;

// ==================== RESPOSTAS DO ESP ====================

// Lê a resposta aos poucos para economizar a memória do Uno.
// O tempo limite vale para a operação inteira.
bool aguardar(const char* esperado, unsigned long limite, bool mostrar) {
  char trecho[32] = "";
  byte usados = 0;
  unsigned long inicio = millis();

  while (millis() - inicio < limite) {
    if (!esp8266.available()) {
      continue;
    }

    char c = esp8266.read();

    if (mostrar) {
      Serial.write(c);
    }

    if (usados == sizeof(trecho) - 1) {
      memmove(trecho, trecho + 1, usados - 1);
      usados--;
    }

    trecho[usados++] = c;
    trecho[usados] = '\0';

    if (strstr(trecho, "WIFI DISCONNECT")) {
      wifiConectado = false;
      return false;
    }

    if (strstr(trecho, esperado)) {
      return true;
    }

    if (strstr(trecho, "\r\nERROR\r\n") ||
        strstr(trecho, "\r\nFAIL\r\n")) {
      return false;
    }
  }

  return false;
}

void limparEntradaESP() {
  while (esp8266.available()) {
    esp8266.read();
  }
}

bool comandoAT(const __FlashStringHelper* comando,
               unsigned long limite) {
  limparEntradaESP();
  esp8266.println(comando);  // Envia o comando com CR e LF
  return aguardar("OK\r\n", limite, true);
}

bool conectarWiFi() {
  Serial.println(F("\nTestando comunicacao com o ESP8266..."));

  if (!comandoAT(F("AT"), 2000)) {
    Serial.println(F("ESP sem resposta. Confira D3, D2 e 9600 baud."));
    return false;
  }

  // Alguns firmwares antigos respondem "no change" quando
  // o modo solicitado já está ativo. A tentativa de conexão
  // abaixo verifica se o módulo consegue entrar na rede.
  comandoAT(F("AT+CWMODE=1"), 2000);

  Serial.print(F("Conectando a rede "));
  Serial.println(REDE);

  limparEntradaESP();
  esp8266.print(F("AT+CWJAP=\""));
  esp8266.print(REDE);
  esp8266.print(F("\",\""));
  esp8266.print(SENHA);
  esp8266.println(F("\""));

  // Oculta o eco deste comando porque ele contém a senha.
  if (!aguardar("OK\r\n", 30000, false)) {
    Serial.println(F("Falha ao entrar no Wi-Fi. Confira a rede e a senha."));
    return false;
  }

  if (!comandoAT(F("AT+CIPMUX=0"), 3000)) {
    Serial.println(F("Nao foi possivel configurar a conexao TCP unica."));
    return false;
  }

  Serial.println(F("Wi-Fi conectado. IP informado pelo ESP:"));
  comandoAT(F("AT+CIFSR"), 2000);
  return true;
}

// ==================== TARA ====================

void zerarBalancas() {
  Serial.println(F("\nRealizando tara das tres prateleiras..."));

  for (byte i = 0; i < TOTAL_BALANCAS; i++) {
    Serial.print(F("Prateleira "));
    Serial.print(PRATELEIRA[i]);

    if (!balancas[i].wait_ready_timeout(1000)) {
      temTara[i] = false;
      Serial.println(F(": HX711 sem resposta. Confira DT e SCK."));
      continue;
    }

    balancas[i].tare();
    temTara[i] = true;
    Serial.println(F(": zerada."));
  }
}

// ==================== ENVIO PARA O PHP ====================

void enviarParaAPI(const char* json) {
  char requisicao[300];

  int tamanho = snprintf_P(
    requisicao, sizeof(requisicao),
    PSTR("POST %s HTTP/1.1\r\n"
         "Host: %s:%u\r\n"
         "Content-Type: application/json\r\n"
         "Content-Length: %u\r\n"
         "Connection: close\r\n"
         "\r\n"
         "%s"),
    CAMINHO, SERVIDOR, PORTA, (unsigned int)strlen(json), json
  );

  if (tamanho < 0 || (size_t)tamanho >= sizeof(requisicao)) {
    Serial.println(F("Requisicao maior que o espaco reservado."));
    return;
  }

  // Abre uma conexão com o Apache para esta prateleira.
  limparEntradaESP();
  esp8266.print(F("AT+CIPSTART=\"TCP\",\""));
  esp8266.print(SERVIDOR);
  esp8266.print(F("\","));
  esp8266.println(PORTA);

  if (!aguardar("OK\r\n", 8000, true)) {
    Serial.println(F("\nFalha TCP. Confira o IP do computador e o Apache."));
    comandoAT(F("AT+CIPCLOSE"), 1500);
    wifiConectado = false;  // Revalida a rede no próximo ciclo
    return;
  }

  esp8266.print(F("AT+CIPSEND="));
  esp8266.println(tamanho);

  if (!aguardar(">", 3000, true)) {
    Serial.println(F("\nESP nao liberou o envio."));
    comandoAT(F("AT+CIPCLOSE"), 1500);
    return;
  }

  // print envia exatamente a quantidade de bytes informada ao ESP.
  esp8266.print(requisicao);

  Serial.println(F("\nResposta do servidor:"));
  if (!aguardar("CLOSED", 8000, true)) {
    Serial.println(F("\nFechamento nao confirmado. Encerrando conexao."));
    comandoAT(F("AT+CIPCLOSE"), 1500);
  }
  Serial.println();
}

// ==================== LEITURA INDIVIDUAL ====================

void lerPrateleira(byte i) {
  Serial.print(F("\nPrateleira "));
  Serial.println(PRATELEIRA[i]);

  if (!temTara[i]) {
    Serial.println(F("Sem tara. Esvazie as tres prateleiras e digite T."));
    return;
  }

  if (!balancas[i].wait_ready_timeout(1000)) {
    Serial.println(F("HX711 sem resposta. Leitura ignorada."));
    return;
  }

  float peso = balancas[i].get_units(5);  // Média de cinco amostras

  if (peso < 0) {
    peso = 0;
  }

  char pesoTexto[20];
  char json[64];
  dtostrf(peso, 1, 2, pesoTexto);

  snprintf(json, sizeof(json),
           "{\"prateleira\":%u,\"peso\":%s}",
           (unsigned int)PRATELEIRA[i], pesoTexto);

  Serial.print(F("Leitura: "));
  Serial.println(json);

  if (wifiConectado) {
    enviarParaAPI(json);
  } else {
    Serial.println(F("Leitura exibida no monitor. Envio indisponivel."));
  }
}

// ==================== INICIALIZAÇÃO E CICLO ====================

void setup() {
  Serial.begin(9600);
  esp8266.begin(9600);

  Serial.println(F("\nArduino Uno - Tres prateleiras"));

  for (byte i = 0; i < TOTAL_BALANCAS; i++) {
    balancas[i].begin(PINO_DT[i], PINO_SCK[i]);
    balancas[i].set_scale(FATOR_CALIBRACAO[i]);
  }

  zerarBalancas();
  Serial.println(F("Digite T para refazer a tara das tres balancas."));
}

void loop() {
  if (Serial.available()) {
    char tecla = Serial.read();

    if (tecla == 'T' || tecla == 't') {
      zerarBalancas();
    }
  }

  if (!primeiroCiclo && millis() - fimDoCiclo < INTERVALO) {
    return;
  }

  primeiroCiclo = false;

  if (!wifiConectado) {
    wifiConectado = conectarWiFi();
  }

  // Uma leitura e um POST por prateleira.
  for (byte i = 0; i < TOTAL_BALANCAS; i++) {
    lerPrateleira(i);
  }

  fimDoCiclo = millis();
}