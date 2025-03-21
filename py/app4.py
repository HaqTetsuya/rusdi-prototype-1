import torch
import pickle
from flask import Flask, request, jsonify
from flask_cors import CORS  
from transformers import AutoTokenizer, AutoModelForSequenceClassification

app = Flask(__name__)
CORS(app) 

model_path = "C:/xampp/htdocs/rusdi-prototype-2/py/indobert_intent_model"

tokenizer = AutoTokenizer.from_pretrained(model_path)
model = AutoModelForSequenceClassification.from_pretrained(model_path)

with open(f"{model_path}/intent_classes.pkl", "rb") as f:
    intent_classes = pickle.load(f)

def predict_intent(text):
    device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
    model.to(device)
    model.eval()

    inputs = tokenizer(text, return_tensors="pt", padding=True, truncation=True, max_length=128)
    inputs = {k: v.to(device) for k, v in inputs.items()}

    with torch.no_grad():
        outputs = model(**inputs)
        logits = outputs.logits
        probabilities = torch.softmax(logits, dim=1).squeeze()

    prediction = torch.argmax(probabilities).item()
    confidence = probabilities[prediction].item()
    predicted_intent = intent_classes[prediction]

    # 🟢 Print semua skor intent di terminal
    print(f"\n[LOG] Input: '{text}'")
    for idx, intent in enumerate(intent_classes):
        print(f"   - {intent}: {probabilities[idx]:.4f}")

    print(f"==> Predicted: '{predicted_intent}' (Confidence: {confidence:.4f})\n")

    return {
        "intent": predicted_intent,
        "confidence": confidence,
        "scores": {intent_classes[i]: probabilities[i].item() for i in range(len(intent_classes))}
    }

@app.route("/analyze", methods=["POST"])
def analyze():
    data = request.get_json()
    text = data.get("text", "")

    if not text:
        return jsonify({"error": "No text provided"}), 400

    result = predict_intent(text)

    return jsonify(result)

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True, use_reloader=False)
