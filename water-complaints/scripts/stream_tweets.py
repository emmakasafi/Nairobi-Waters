import tweepy
import json
import subprocess
from datetime import datetime

# X API Bearer Token
BEARER_TOKEN = "AAAAAAAAAAAAAAAAAAAAADVDwgEAAAAATfbPRJM1Bt%2FvvkjuqP98KPuNg5c%3DM3P47Sy4TRDJRXSKI7z8yvaBLSPuLrAhayObQITJsyGKzk0Mqu"  # Replace with your token

# Initialize client
try:
    client = tweepy.Client(bearer_token=BEARER_TOKEN)
except Exception as e:
    print(f"Error initializing client: {e}")
    exit(1)

# Stream tweets
class TweetPrinter(tweepy.StreamingClient):
    def on_tweet(self, tweet):
        # Call analyze_tweet.py
        result = subprocess.run(
            ['python', 'C:/Users/emmah/Desktop/FOURTH YEAR PROJECT/Nairobi-Waters/water-complaints/scripts/analyze_tweet.py', tweet.text],
            capture_output=True,
            text=True
        )
        try:
            analysis = json.loads(result.stdout)
            if "error" in analysis:
                print(f"Error analyzing tweet: {analysis['error']}")
                return
            
            # Prepare data
            tweet_data = {
                "tweet_id": str(tweet.id),
                "text": analysis["text"],
                "created_at": datetime.now().isoformat(),
                "user_handle": str(tweet.author_id),
                "sentiment_score": analysis["sentiment_score"],
                "sentiment_label": analysis["sentiment_label"],
                "keywords": analysis["keywords"],
                "language": analysis["language"],
                "location": "Nairobi",  # Update with tweet.place if available
                "category": analysis["category"]
            }
            # Output JSON for Laravel
            print(json.dumps(tweet_data))
        
        except json.JSONDecodeError as e:
            print(f"Error decoding analysis: {e}")

    def on_error(self, status):
        print(f"Stream error: {status}")

    def on_exception(self, exception):
        print(f"Exception: {exception}")

# Try streaming, fallback to search if streaming fails
try:
    printer = TweetPrinter(BEARER_TOKEN)
    # Clear existing rules
    rules = printer.get_rules()
    if rules.data:
        printer.delete_rules([rule.id for rule in rules.data])
    # Add new rule
    printer.add_rules(tweepy.StreamRule("(water OR maji OR @Nbiwater_Care) (shortage OR uhaba OR quality OR maji machafu OR billing OR sewer OR rationing OR leak)"))
    printer.filter()
except Exception as e:
    print(f"Streaming failed: {e}. Falling back to search...")
    # Fallback to search
    try:
        tweets = client.search_recent_tweets(
            query="(water OR maji OR @Nbiwater_Care) (shortage OR uhaba OR quality OR maji machafu OR billing OR sewer OR rationing OR leak)",
            max_results=10
        )
        if tweets.data:
            for tweet in tweets.data:
                result = subprocess.run(
                    ['python', 'C:/Users/emmah/Desktop/FOURTH YEAR PROJECT/Nairobi-Waters/water-complaints/scripts/analyze_tweet.py', tweet.text],
                    capture_output=True,
                    text=True
                )
                analysis = json.loads(result.stdout)
                if "error" in analysis:
                    continue
                tweet_data = {
                    "tweet_id": str(tweet.id),
                    "text": analysis["text"],
                    "created_at": datetime.now().isoformat(),
                    "user_handle": str(tweet.author_id),
                    "sentiment_score": analysis["sentiment_score"],
                    "sentiment_label": analysis["sentiment_label"],
                    "keywords": analysis["keywords"],
                    "language": analysis["language"],
                    "location": "Nairobi",
                    "category": analysis["category"]
                }
                print(json.dumps(tweet_data))
        else:
            print("No tweets found.")
    except Exception as e:
        print(f"Search failed: {e}")