from flask import Flask, request
import win32print

app = Flask(__name__)

@app.route('/imprimir', methods=['POST'])
def imprimir():
    data = request.json
    zpl = data['zpl']
    
    # Nome da impressora (verifique se está correto)
    printer_name = "ZDesigner ZT410-203dpi ZPL"

    # Enviar para a impressora
    try:
        handle = win32print.OpenPrinter(printer_name)
        job = win32print.StartDocPrinter(handle, 1, ("Etiqueta", None, "RAW"))
        win32print.StartPagePrinter(handle)
        win32print.WritePrinter(handle, zpl.encode())
        win32print.EndPagePrinter(handle)
        win32print.EndDocPrinter(handle)
        win32print.ClosePrinter(handle)
        return "Impressão realizada com sucesso", 200
    except Exception as e:
        return str(e), 500

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000)








