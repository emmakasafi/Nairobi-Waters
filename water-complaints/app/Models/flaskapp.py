from flask import Flask, request, jsonify
from flask_cors import CORS  
from transformers import RobertaForSequenceClassification, RobertaTokenizer, pipeline
import torch
import re
import nltk
import logging
from nltk.tokenize import word_tokenize
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer

# Configure logging
logging.basicConfig(level=logging.INFO)

# Specify NLTK download path
nltk.data.path.append(r"C:\Users\emmah\AppData\Roaming\nltk_data")

# Download necessary NLTK resources
nltk.download("punkt")
nltk.download("stopwords")
nltk.download("wordnet")

app = Flask(__name__)
CORS(app)

# Load Sentiment Model 
MODEL_PATH = r"C:\Users\emmah\Downloads\roberta_model-20250213T114406Z-001\roberta_model"

try:
    tokenizer = RobertaTokenizer.from_pretrained(MODEL_PATH)
    model = RobertaForSequenceClassification.from_pretrained(MODEL_PATH)
    model.eval()
except Exception as e:
    logging.error(f"Error loading model: {e}")

# Load Zero-Shot Classification Model
zero_shot_classifier = pipeline("zero-shot-classification", model="facebook/bart-large-mnli")

# Complaint Categories 
water_categories = [
    "Billing Issue", "Water Shortage", "Water Quality",
    "Leakage or Pipe Burst", "Sewerage Issue", "Meter Issues",
    "Customer Service", "Other"
]

# Text Preprocessing Function
def preprocess_text(text):
    """ Cleans and preprocesses complaint text. """
    text = text.lower()
    text = re.sub(r"[^\w\s']", "", text)  
    words = word_tokenize(text)
    stop_words = set(stopwords.words("english")) - {"not", "no", "nor", "never"}  
    words = [word for word in words if word not in stop_words]
    lemmatizer = WordNetLemmatizer()
    words = [lemmatizer.lemmatize(word) for word in words]
    return " ".join(words)


def analyze_complaint(original_text):
    """
    Analyzes complaint sentiment and classifies category.
    Returns both the original and processed text.
    """
    processed_text = preprocess_text(original_text)

    zero_shot_result = zero_shot_classifier(processed_text, water_categories)
    predicted_category = zero_shot_result["labels"][0]  

    inputs = tokenizer(processed_text, return_tensors="pt", truncation=True, padding=True)
    with torch.no_grad():
        outputs = model(**inputs).logits

    probabilities = torch.nn.functional.softmax(outputs, dim=1)[0]
    sentiment_labels = ["negative", "neutral", "positive"]
    sentiment = sentiment_labels[torch.argmax(probabilities).item()]

    return {
        "original_caption": original_text,  
        "processed_caption": processed_text,  
        "sentiment": sentiment,
        "category": predicted_category
    }

@app.route("/analyze", methods=["POST"])
def analyze():
    """ API endpoint to analyze complaints. """
    data = request.json
    complaint_text = data.get("complaint", "").strip()

    if not complaint_text:
        return jsonify({"error": "No complaint text provided"}), 400

    result = analyze_complaint(complaint_text)
    return jsonify(result) 

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001, debug=True)