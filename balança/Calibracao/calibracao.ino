// calibração e calculo de fator

#include "HX711.h"
#include <stdlib.h>
#include <math.h>

const byte TOTAL_BALANCAS = 3;

//                           P1  P2  P3
const byte PINO_DT[]      = {4,  6,  8};
const byte PINO_SCK[]     = {5,  7,  9};

const byte AMOSTRAS = 20;

HX711 balancas[TOTAL_BALANCAS];
float fatores[TOTAL_BALANCAS];
float massaConhecida;

// Aguarda o envio de uma linha pelo Monitor Serial.
// Aceita tanto "Nova linha" quanto "Ambos, NL e CR".
void lerLinha(char* texto, byte capacidade) {
  byte usados = 0;
  bool excedeu = false;

  while (true) {
    if (!Serial.available()) {
      continue;
    }

    char caractere = Serial.read();

    if (caractere == '\r') {
      continue;
    }

    if (caractere == '\n') {
      texto[excedeu ? 0 : usados] = '\0';
      return;
    }

    if (usados < capacidade - 1) {
      texto[usados++] = caractere;
    } else {
      excedeu = true;
    }
  }
}

void aguardarConfirmacao() {
  char resposta[8];

  do {
    Serial.println(F("Digite C e clique em Enviar para continuar."));
    lerLinha(resposta, sizeof(resposta));
  } while (!((resposta[0] == 'C' || resposta[0] == 'c') &&
             resposta[1] == '\0'));
}

float pedirMassa() {
  char texto[24];

  while (true) {
    Serial.println(F("Informe a massa conhecida em gramas. Exemplo: 500"));
    lerLinha(texto, sizeof(texto));

    // Permite digitar, por exemplo, 250,5 ou 250.5.
    for (byte i = 0; texto[i] != '\0'; i++) {
      if (texto[i] == ',') {
        texto[i] = '.';
      }
    }

    char* fim;
    float massa = strtod(texto, &fim);

    if (fim != texto && *fim == '\0' && isfinite(massa) && massa > 0) {
      return massa;
    }

    Serial.println(F("Digite somente um numero maior que zero, sem a letra g."));
  }
}

// Lê diretamente o HX711. O fator anterior não entra no cálculo.
// Cada amostra tem seu próprio tempo limite.
bool lerMediaBruta(byte indice, long& media) {
  long soma = 0;

  for (byte n = 0; n < AMOSTRAS; n++) {
    if (!balancas[indice].wait_ready_timeout(1000)) {
      return false;
    }
    soma += balancas[indice].read();
  }

  media = soma / AMOSTRAS;
  return true;
}

long medirComConfirmacao(byte indice) {
  long media;

  while (true) {
    aguardarConfirmacao();
    Serial.println(F("Medindo... mantenha a prateleira parada."));

    if (lerMediaBruta(indice, media)) {
      return media;
    }

    Serial.println(F("HX711 sem resposta. Confira as ligacoes desta balanca."));
  }
}

void calibrarBalanca(byte indice) {
  Serial.println();
  Serial.print(F("=== PRATELEIRA "));
  Serial.print(indice + 1);
  Serial.println(F(" ==="));

  Serial.println(F("1. Deixe esta prateleira VAZIA."));
  long leituraVazia = medirComConfirmacao(indice);

  Serial.print(F("2. Coloque o objeto de "));
  Serial.print(massaConhecida, 2);
  Serial.println(F(" g no centro desta prateleira."));

  while (true) {
    long leituraComPeso = medirComConfirmacao(indice);
    long diferenca = leituraComPeso - leituraVazia;

    if (diferenca != 0) {
      // Preserva o sinal: algumas montagens precisam de fator negativo.
      fatores[indice] = diferenca / massaConhecida;
      break;
    }

    Serial.println(F("A leitura nao mudou. Confira o objeto e a celula de carga."));
  }

  Serial.print(F("Fator da prateleira "));
  Serial.print(indice + 1);
  Serial.print(F(" = "));
  Serial.println(fatores[indice], 4);
  Serial.println(F("Retire o objeto desta prateleira."));
}

void setup() {
  Serial.begin(9600);

  for (byte i = 0; i < TOTAL_BALANCAS; i++) {
    balancas[i].begin(PINO_DT[i], PINO_SCK[i]);
  }

  Serial.println(F("\nCALIBRACAO DAS TRES BALANCAS"));
  massaConhecida = pedirMassa();

  for (byte i = 0; i < TOTAL_BALANCAS; i++) {
    calibrarBalanca(i);
  }

  Serial.println(F("\nCopie esta linha para o seu codigo principal:"));
  Serial.print(F("const float FATOR_CALIBRACAO[] = {"));

  for (byte i = 0; i < TOTAL_BALANCAS; i++) {
    if (i > 0) {
      Serial.print(F(", "));
    }
    Serial.print(fatores[i], 4);
  }

  Serial.println(F("};"));
  Serial.println(F("Para repetir a calibracao, pressione RESET no Arduino."));
}

void loop() {
  // A calibração é executada uma vez, no setup().
}