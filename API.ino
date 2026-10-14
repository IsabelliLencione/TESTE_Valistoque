#include <SoftwareSerial.h>
#include "HX711.h"

// Configuração do Módulo HX711
const int LOADCELL_DOUT_PIN = 4;
const int LOADCELL_SCK_PIN = 5;
HX711 balanca;
float fator_calibracao = 420.0; // Ajuste conforme a calibração da sua célula em gramas

// Configuração do ESP8266 (RoboCore)
// Pino 3 do Arduino (RX) <-> TX do ESP8266
// Pino 2 do Arduino (TX) <-> RX do ESP8266
SoftwareSerial esp8266(3, 2); 

// Dados da Aplicação, Rede e Servidor
int numero_prateleira = 1;
String nome_rede = "NATHAN_SENAI";
String senha_rede = "12345678";
String ip_servidor = "10.136.86.239"; 
String porta_servidor = "80";

void setup() {
  Serial.begin(9600);
  esp8266.begin(9600);
  
  // Inicializa o HX711
  balanca.begin(LOADCELL_DOUT_PIN, LOADCELL_SCK_PIN);
  balanca.set_scale(fator_calibracao);
  
  Serial.println("Zerando balança (Tara)...");
  balanca.tare();
  
  // Conecta o ESP8266 ao Wi-Fi
  conectarWiFi();
  
  Serial.println("\n--- SISTEMA PRONTO ---");
  Serial.println("Digite 't' no Monitor Serial para refazer a Tara.");
}

void loop() {
  // Tara manual via Monitor Serial
  if (Serial.available() > 0) {
    char c = Serial.read();
    if (c == 't' || c == 'T') {
      Serial.println("Refazendo Tara...");
      balanca.tare();
      Serial.println("Balança zerada com sucesso!");
    }
  }

  // Leitura do peso em gramas com casas decimais (float)
  float peso_gramas = balanca.get_units(5);
  if (peso_gramas < 0.0) peso_gramas = 0.0; // Evita ruídos negativos pequenos

  // Monta o JSON exato: {"prateleira":1,"peso":86.48}
  String jsonPayload = "{\"prateleira\":" + String(numero_prateleira) + ",\"peso\":" + String(peso_gramas, 2) + "}";

  Serial.print("\nEnviando: ");
  Serial.println(jsonPayload);

  enviarParaAPI(jsonPayload);

  delay(10000); // Intervalo de 10 segundos entre envios
}

void conectarWiFi() {
  Serial.println("Configurando ESP8266...");
  enviarComandoAT("AT+RST", 3000);
  enviarComandoAT("AT+CWMODE=1", 1000);
  
  Serial.println("Conectando ao WiFi...");
  enviarComandoAT("AT+CWJAP=\"" + nome_rede + "\",\"" + senha_rede + "\"", 8000);
  
  delay(2000);
  enviarComandoAT("AT+CIFSR", 2000); // Exibe o IP obtido
}

void enviarParaAPI(String json) {
  limparBufferESP();

  // 1. Inicia conexão TCP
  esp8266.println("AT+CIPSTART=\"TCP\",\"" + ip_servidor + "\"," + porta_servidor);
  delay(2000);
  
  if (!aguardarResposta("OK", 2000)) {
    Serial.println("[ERRO] Falha na conexão TCP. Reconectando WiFi...");
    conectarWiFi();
    return;
  }

  // 2. Monta o pacote HTTP POST para /TCC/api/api.php
  String httpReq = "POST /TCC/api/api.php HTTP/1.1\r\n";
  httpReq += "Host: " + ip_servidor + "\r\n";
  httpReq += "Content-Type: application/json\r\n";
  httpReq += "Content-Length: " + String(json.length()) + "\r\n";
  httpReq += "Connection: close\r\n\r\n";
  httpReq += json;

  // 3. Informa o tamanho total dos dados
  esp8266.println("AT+CIPSEND=" + String(httpReq.length()));
  delay(1000);
  
  // 4. Envia a requisição HTTP
  esp8266.print(httpReq);
  delay(2000);
  
  lerRespostaESP();

  // 5. Encerra a conexão
  esp8266.println("AT+CIPCLOSE");
  delay(500);
}

void enviarComandoAT(String comando, const int timeout) {
  limparBufferESP();
  esp8266.println(comando);
  long int tempo = millis();
  while ((millis() - tempo) < timeout) {
    while (esp8266.available()) {
      Serial.write(esp8266.read());
    }
  }
}

bool aguardarResposta(String textoChave, const int timeout) {
  long int tempo = millis();
  String resposta = "";
  while ((millis() - tempo) < timeout) {
    while (esp8266.available()) {
      char c = esp8266.read();
      resposta += c;
      Serial.write(c);
      if (resposta.indexOf(textoChave) != -1) {
        return true;
      }
    }
  }
  return false;
}

void limparBufferESP() {
  while (esp8266.available()) {
    esp8266.read();
  }
}

void lerRespostaESP() {
  while (esp8266.available()) {
    Serial.write(esp8266.read());
  }
}
