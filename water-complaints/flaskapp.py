from flask import Flask, request, jsonify, g
from flask_cors import CORS
from transformers import RobertaForSequenceClassification, RobertaTokenizer, pipeline
import torch
import re
import nltk
import logging
from nltk.tokenize import word_tokenize
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer
from sqlalchemy import create_engine, Column, String, DateTime, Integer
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
from datetime import datetime, timezone

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

# Database configuration
DATABASE_URI = 'postgresql://postgres:Emma321.Postgresql@localhost:5432/water_complaints'
engine = create_engine(DATABASE_URI)
Base = declarative_base()
Session = sessionmaker(bind=engine)
session = Session()

# Define the WaterSentiment model
class WaterSentiment(Base):
    __tablename__ = 'water_sentiments'
    id = Column(Integer, primary_key=True)
    original_caption = Column(String)
    processed_caption = Column(String)
    timestamp = Column(DateTime, default=datetime.now(timezone.utc))
    overall_sentiment = Column(String)
    complaint_category = Column(String)
    source = Column(String)
    subcounty = Column(String)
    ward = Column(String)
    user_id = Column(Integer)
    user_name = Column(String)
    user_email = Column(String)
    user_phone = Column(String)
    status = Column(String)
    entity_type = Column(String)
    entity_name = Column(String)

# Create the table if it doesn't exist
Base.metadata.create_all(engine)

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
    
    "Billing and Payments",
    "Water Supply and Distribution",
    "Water Quality and Testing",
    "Pipe Leaks and Maintenance",
    "Sewage and Sanitation",
    "Metering Services",
    "Customer Support and Engagement",
    "Infrastructure and Projects",
    "Environmental and Conservation",
    "ICT and System Access"

]

# Text Preprocessing Function
def preprocess_text(text):
    """" Cleans and preprocesses complaint text. """
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

# Function to reset the auto-increment sequence
def reset_sequence():
    try:
        # Get the current session's connection and execute the query
        with engine.connect() as connection:
            connection.execute("""
                SELECT setval(pg_get_serial_sequence('water_sentiments', 'id'), 
                (SELECT MAX(id) FROM water_sentiments));
            """)
            connection.commit()
    except Exception as e:
        logging.error(f"Error resetting sequence: {e}")

@app.route("/analyze", methods=["POST"])
def analyze():
    """ API endpoint to analyze complaints. """
    try:
        data = request.json
        complaint_text = data.get("complaint", "").strip()
        logging.info(f"Received complaint text: {complaint_text}")

        if not complaint_text:
            logging.error("No complaint text provided")
            return jsonify({"error": "No complaint text provided"}), 400

        result = analyze_complaint(complaint_text)

        # Retrieve user details from the session or authentication context
        user_id = data.get('user_id', "")  
        user_name = data.get('user_name', "") 
        user_email = data.get('user_email', "")  
        user_phone = data.get("user_phone", "")  # Get user_phone from the request

        # Create a new WaterSentiment record (no need to specify 'id')
        new_record = WaterSentiment(
            original_caption=result["original_caption"],
            processed_caption=result["processed_caption"],
            timestamp=datetime.now(timezone.utc),  # Use timezone-aware UTC timestamp
            overall_sentiment=result["sentiment"],
            complaint_category=result["category"],
            source="web_form",  # Assuming the source is a web form
            subcounty=data.get("subcounty", ""),
            ward=data.get("ward", ""),
            user_id=user_id,
            user_name=user_name,
            user_email=user_email,
            user_phone=user_phone,
            status="pending",  # Assuming the status is pending initially
            entity_type=data.get("entity_type", ""),
            entity_name=data.get("entity_name", "")
        )
        session.add(new_record)
        session.commit()

        # Reset the sequence after inserting a record
        reset_sequence()

        return jsonify(result)
    except Exception as e:
        logging.error(f"Error analyzing complaint: {e}")
        return jsonify({"error": "Internal server error"}), 500

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001, debug=True)