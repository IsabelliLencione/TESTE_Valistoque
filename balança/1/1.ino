/* 
  Código de Leitura Direta (Sem Calibração)
  Busca o fator calibrado diretamente da EEPROM e exibe o peso a cada 10 segundos.
*/

#include <HX711_ADC.h>
#if defined(ESP8266)|| defined(ESP32) || defined(AVR)
#include <EEPROM.h>
#endif

// Pinos de conexão (mantenha os mesmos do código anterior):
const int HX711_dout = 4; // MCU > HX711 pino DOUT
const int HX711_sck = 5;  // MCU > HX711 pino SCK

// Construtor do HX711:
HX711_ADC LoadCell(HX711_dout, HX711_sck);

// O endereço deve ser exatamente o mesmo onde foi salvo (0):
const int calVal_eepromAdress = 0; 
unsigned long t = 0;

void setup() {
  Serial.begin(57600);
  delay(10);
  Serial.println();
  Serial.println("Iniciando balança...");

  LoadCell.begin();
  unsigned long stabilizingtime = 2000; // Tempo para estabilizar após ligar
  boolean _tare = true;                 // Zera a balança automaticamente ao ligar (garanta que ela esteja vazia ao ligar)
  
  LoadCell.start(stabilizingtime, _tare);
  
  if (LoadCell.getTareTimeoutFlag() || LoadCell.getSignalTimeoutFlag()) {
    Serial.println("Erro: Falha na comunicação com o HX711. Verifique a fiação.");
    while (1);
  } else {
    // --- ABRE A EEPROM E BUSCA O VALOR SALVO ---
    float calibrationValue;
    #if defined(ESP8266)|| defined(ESP32)
    EEPROM.begin(512);
    #endif
    
    // Lê o valor guardado na memória
    EEPROM.get(calVal_eepromAdress, calibrationValue); 
    
    // Aplica o fator de calibração recuperado na balança
    LoadCell.setCalFactor(calibrationValue); 
    
    Serial.print("Fator de calibração recuperado da memória: ");
    Serial.println(calibrationValue);
    Serial.println("Inicialização concluída com sucesso!");
  }
  
  while (!LoadCell.update());
}

void loop() {
  static boolean newDataReady = 0;
  const int serialPrintInterval = 10000; // Exibe o resultado a cada 10 segundos

  // Verifica se há novos dados da balança
  if (LoadCell.update()) newDataReady = true;

  // Mostra o peso no monitor serial a cada 10 segundos
  if (newDataReady) {
    if (millis() > t + serialPrintInterval) {
      float peso = LoadCell.getData();
      Serial.print("Peso atual: ");
      Serial.println(peso);
      newDataReady = 0;
      t = millis();
    }
  }

  // Se você precisar zerar a balança manualmente enquanto ela estiver ligada,
  // basta digitar 't' e dar enter no Monitor Serial
  if (Serial.available() > 0) {
    char inByte = Serial.read();
    if (inByte == 't') {
      LoadCell.tareNoDelay();
    }
  }

  if (LoadCell.getTareStatus() == true) {
    Serial.println("Balança zerada (Tara concluída)");
  }
}
