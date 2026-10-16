import serial
import requests
import time

PORTA = "COM3"
BAUDRATE = 9600

URL = "http://localhost/TESTE_Valistoque/balança/enviar_peso.php"

PRATELEIRA = 1

try:

    arduino = serial.Serial(
        PORTA,
        BAUDRATE,
        timeout=1
    )

    time.sleep(2)

    print("Arduino conectado!")
    print("Aguardando peso...")

except Exception as erro:

    print("Erro ao conectar com o Arduino:")
    print(erro)

    exit()


while True:

    try:

        linha = arduino.readline().decode(
            "utf-8",
            errors="ignore"
        ).strip()

        if linha.startswith("PESO:"):

            valor = linha.replace(
                "PESO:",
                ""
            ).strip()

            peso = float(valor)

            print(
                "Peso recebido:",
                peso,
                "kg"
            )

            dados = {
                "peso": peso,
                "prateleira": PRATELEIRA
            }

            resposta = requests.post(
                URL,
                data=dados,
                timeout=5
            )

            resultado = resposta.json()

            if resultado.get("sucesso"):

                print(
                    "Peso enviado para a prateleira",
                    PRATELEIRA
                )

                print(
                    "Peso:",
                    resultado["peso_kg"],
                    "kg"
                )

            else:

                print(
                    "Erro:",
                    resultado.get("erro")
                )

        time.sleep(0.2)

    except ValueError:

        pass

    except requests.exceptions.RequestException:

        print("Erro ao conectar com o PHP")

        time.sleep(2)

    except KeyboardInterrupt:

        print("\nPrograma encerrado.")

        break

    except Exception as erro:

        print("Erro:", erro)

        time.sleep(1)