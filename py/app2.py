from flask import Flask, request, jsonify
from transformers import AutoModelForSequenceClassification, AutoTokenizer
import pickle
import torch
import numpy as np
import os
import json

app = Flask(__name__)

# Global variables to store loaded model, tokenizer, and other resources
MODEL_SAVE_PATH = "C:/xampp/htdocs/rusdi-prototype-2/py/indobert_intent_model"  # Your existing path
model = None
tokenizer = None
intent_classes = None
thresholds = None

def load_ood_thresholds(model_path):
    """Load the OOD thresholds from the model directory - using JSON instead of pickle."""
    # Look for JSON file instead of pickle
    threshold_path = os.path.join(model_path, "ood_thresholds.json")
    
    # Check if file exists before attempting to open
    if os.path.exists(threshold_path):
        with open(threshold_path, "r") as f:
            return json.load(f)
    else:
        # Provide default thresholds if file not found
        print(f"Warning: Threshold file not found at {threshold_path}. Using default values.")
        return {
            "energy_threshold": 0.0,  # Replace with your default value
            "msp_threshold": 0.5      # Replace with your default value
        }

def load_resources():
    """Load model, tokenizer, intent classes, and thresholds."""
    global model, tokenizer, intent_classes, thresholds
    
    print(f"Loading resources from {MODEL_SAVE_PATH}...")
    
    # Load model and tokenizer
    model = AutoModelForSequenceClassification.from_pretrained(MODEL_SAVE_PATH)
    tokenizer = AutoTokenizer.from_pretrained(MODEL_SAVE_PATH)
    
    # Load intent classes
    intent_classes_path = os.path.join(MODEL_SAVE_PATH, "intent_classes.pkl")
    if os.path.exists(intent_classes_path):
        with open(intent_classes_path, "rb") as f:
            intent_classes = pickle.load(f)
    else:
        raise FileNotFoundError(f"Intent classes file not found at {intent_classes_path}")
    
    # Load OOD thresholds
    thresholds = load_ood_thresholds(MODEL_SAVE_PATH)
    
    print("Resources loaded successfully")
    print(f"Loaded {len(intent_classes)} intent classes")
    print(f"Thresholds: {thresholds}")

def predict_intent_with_enhanced_ood(text, model, tokenizer, intent_classes, 
                                    energy_threshold, msp_threshold, method='combined'):
    """
    Predict intent with enhanced out-of-distribution detection.
    
    Args:
        text: Input text to classify
        model: Loaded model
        tokenizer: Loaded tokenizer
        intent_classes: List of intent classes
        energy_threshold: Threshold for energy-based OOD detection
        msp_threshold: Threshold for maximum softmax probability OOD detection
        method: OOD detection method ('energy', 'msp', or 'combined')
        
    Returns:
        Dictionary with predicted intent and OOD flag
    """
    # Tokenize input
    inputs = tokenizer(text, return_tensors="pt", padding=True, truncation=True, max_length=512)
    
    # Get model outputs
    with torch.no_grad():
        outputs = model(**inputs)
        logits = outputs.logits
    
    # Get probabilities
    probs = torch.nn.functional.softmax(logits, dim=-1)
    max_prob, pred_idx = torch.max(probs, dim=-1)
    
    # Calculate energy score
    energy = -torch.logsumexp(logits, dim=-1)
    
    # Make OOD decision based on selected method
    is_ood = False
    if method == 'energy':
        is_ood = energy.item() > energy_threshold
    elif method == 'msp':
        is_ood = max_prob.item() < msp_threshold
    elif method == 'combined':
        is_ood = (energy.item() > energy_threshold) or (max_prob.item() < msp_threshold)
    
    # Get predicted intent class
    predicted_intent = intent_classes[pred_idx.item()] if not is_ood else "out_of_distribution"
    
    return {
        "intent": predicted_intent,
        "is_ood": is_ood,
        "confidence": max_prob.item(),
        "energy_score": energy.item()
    }

@app.route('/predict_intent', methods=['POST'])
def predict_intent():
    """Endpoint to predict intent from text."""
    # Check if request contains JSON
    if not request.is_json:
        return jsonify({"error": "Request must be JSON"}), 400
    
    # Get text from request
    data = request.get_json()
    if 'text' not in data:
        return jsonify({"error": "Missing 'text' field in request"}), 400
    
    text = data['text']
    
    # Default to combined method unless specified
    method = data.get('method', 'combined')
    if method not in ['energy', 'msp', 'combined']:
        return jsonify({"error": "Invalid method. Must be 'energy', 'msp', or 'combined'"}), 400
    
    # Make prediction
    result = predict_intent_with_enhanced_ood(
        text, 
        model, 
        tokenizer, 
        intent_classes, 
        thresholds["energy_threshold"],
        thresholds["msp_threshold"],
        method=method
    )
    
    # Return prediction as JSON
    return jsonify(result)

# Simple test endpoint
@app.route('/test', methods=['GET'])
def test():
    return jsonify({"status": "API is running", "model_path": MODEL_SAVE_PATH})

if __name__ == '__main__':
    # Load resources only once before starting the server
    load_resources()
    
    # Option 1: Keep debug mode but disable auto-reloader
    app.run(debug=True, use_reloader=False, host='0.0.0.0', port=5000)
    
    # Option 2 (Alternative): Disable debug mode entirely for production
    # app.run(debug=False, host='0.0.0.0', port=5000)