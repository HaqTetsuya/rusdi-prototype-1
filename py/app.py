from flask import Flask, request, jsonify
from flask_cors import CORS  

app = Flask(__name__)
CORS(app)  

intent_mapping = {
    '/greet': 'greeting',
    'help': 'bantuan',
    'book': 'pinjam_buku',
    'search': 'cari_buku',
    'schedule': 'jadwal_perpus'
}

@app.route('/analyze', methods=['POST'])
def analyze():
    data = request.get_json()  # Ambil data JSON dari request
    user_input = data.get('text', '')
    print(user_input)
    # Cek apakah input sesuai dengan intent command
    intent = intent_mapping.get(user_input, 'unknown')

    return jsonify({'intent': intent})


if __name__ == '__main__':
    app.run(debug=True)
