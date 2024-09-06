from flask import Flask, request
import win32print
import tkinter as tk
from tkinter import simpledialog
import threading
import os
import signal
import json

app = Flask(__name__)

# Arquivo para armazenar o nome da impressora
NOME_IMPRESSORA_ARQUIVO = "nome_impressora.json"

# Função para obter o nome da impressora
def obter_nome_impressora():
    root = tk.Tk()
    root.withdraw()  # Oculta a janela principal
    nome_impressora = simpledialog.askstring("Nome da Impressora", "Digite o nome de compartilhamento da impressora:")
    root.destroy()  # Fecha a interface gráfica
    return nome_impressora

# Função para carregar o nome da impressora do arquivo
def carregar_nome_impressora():
    if os.path.exists(NOME_IMPRESSORA_ARQUIVO):
        with open(NOME_IMPRESSORA_ARQUIVO, "r") as f:
            data = json.load(f)
            return data.get("nome_impressora")
    return None

# Função para salvar o nome da impressora no arquivo
def salvar_nome_impressora(nome_impressora):
    with open(NOME_IMPRESSORA_ARQUIVO, "w") as f:
        json.dump({"nome_impressora": nome_impressora}, f)

@app.route('/imprimir', methods=['POST'])
def imprimir():
    try:
        data = request.json
        zpl = data['zpl']
        
        # Carregar o nome da impressora
        printer_name = carregar_nome_impressora()
        
        if not printer_name:
            # Solicita o nome da impressora ao usuário se não estiver salvo
            printer_name = obter_nome_impressora()
            if not printer_name:
                return "Nome da impressora não fornecido", 400
            salvar_nome_impressora(printer_name)

        # Enviar diretamente para a impressora
        handle = win32print.OpenPrinter(printer_name)
        job = win32print.StartDocPrinter(handle, 1, ("Etiqueta", None, "RAW"))
        win32print.StartPagePrinter(handle)
        win32print.WritePrinter(handle, zpl.encode())
        win32print.EndPagePrinter(handle)
        win32print.EndDocPrinter(handle)
        win32print.ClosePrinter(handle)
        
        return "Impressão realizada com sucesso", 200
    
    except Exception as e:
        print(f"Erro ao imprimir: {e}")  # Log do erro no console
        return str(e), 500

@app.route('/stop', methods=['POST'])
def stop():
    os.kill(os.getpid(), signal.SIGTERM)
    return "Servidor encerrado", 200

def run_flask():
    app.run(host='0.0.0.0', port=5000, debug=False)  # host='0.0.0.0' permite conexões externas

if __name__ == "__main__":
    # Verifica se o nome da impressora já está salvo
    if not carregar_nome_impressora():
        obter_nome_impressora()  # Solicita o nome da impressora na primeira execução
    
    # Executa o servidor Flask em uma thread separada
    threading.Thread(target=run_flask).start()
    
    # Para garantir que o código principal não fique bloqueado
    while True:
        pass
