import sys
import json
from vaderSentiment.vaderSentiment import SentimentIntensityAnalyzer
from googletrans import Translator
import re

# Initialize
analyzer = SentimentIntensityAnalyzer()
translator = Translator()

# Clean text
def clean_text(text):
    text = re.sub(r"http\S+|www\S+|https\S+", "", text, flags=re.MULTILINE)  # Remove URLs
    text = re.sub(r"@\w+|\#\w+", "", text)  # Remove mentions and hashtags
    text = re.sub(r"[^\w\s]", "", text)  # Remove special characters
    return text.strip()

# Translate Swahili to English
def translate_to_english(text):
    try:
        detected = translator.detect(text)
        if detected.lang == "sw":
            translated = translator.translate(text, dest="en").text
            return translated, "sw"
        return text, "en"
    except:
        return text, "other"

# Extract keywords
def extract_keywords(text):
    keywords = ["shortage", "rationing", "quality", "dirty", "billing", "leak", "sewer", "maji", "uhaba", "maji machafu"]
    found = [kw for kw in keywords if kw.lower() in text.lower()]
    return ",".join(found) if found else "none"

# Categorize complaint
def categorize_complaint(text):
    lowercase_text = text.lower()
    if "billing" in lowercase_text or "bill" in lowercase_text:
        return "billing"
    elif "shortage" in lowercase_text or "uhaba" in lowercase_text or "rationing" in lowercase_text:
        return "shortage"
    elif "quality" in lowercase_text or "maji machafu" in lowercase_text or "dirty" in lowercase_text:
        return "quality"
    elif "leak" in lowercase_text or "sewer" in lowercase_text:
        return "infrastructure"
    else:
        return "other"

# Sentiment analysis
def analyze_sentiment(text):
    scores = analyzer.polarity_scores(text)
    compound = scores["compound"]
    if compound >= 0.05:
        label = "positive"
    elif compound <= -0.05:
        label = "negative"
    else:
        label = "neutral"
    return compound, label

# Main processing
def process_tweet(tweet_text):
    if not tweet_text:
        return {"error": "Empty tweet"}
    
    # Clean and translate
    cleaned_text = clean_text(tweet_text)
    if not cleaned_text:
        return {"error": "Cleaned text is empty"}
    
    translated_text, language = translate_to_english(cleaned_text)
    
    # Analyze
    sentiment_score, sentiment_label = analyze_sentiment(translated_text)
    keywords = extract_keywords(cleaned_text)
    category = categorize_complaint(cleaned_text)
    
    # Return JSON
    return {
        "text": cleaned_text,
        "sentiment_score": sentiment_score,
        "sentiment_label": sentiment_label,
        "keywords": keywords,
        "language": language,
        "category": category
    }

# Command-line execution
if __name__ == "__main__":
    try:
        tweet_text = sys.argv[1] if len(sys.argv) > 1 else ""
        result = process_tweet(tweet_text)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({"error": str(e)}))