from flask import Flask, request, jsonify
import numpy as np
import pandas as pd
import pickle
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity

# ⚙️ Initialize Flask
app = Flask(__name__)

# ⚙️ Load once: lightweight and memory safe
print("🔄 Loading metadata and embeddings...")
embedding_path = "book_embeddings.npy"
metadata_path = "books_metadata.pkl"

book_embeddings = np.load(embedding_path, mmap_mode='r')  # mmap avoids loading all into RAM
with open(metadata_path, 'rb') as f:
    df = pickle.load(f)

# ⚙️ Load lightweight model ONCE
model = SentenceTransformer('all-MiniLM-L6-v2', device='cpu')  # force CPU

print(f"✅ Loaded {len(df)} books. Using CPU mode with memory mapping.")

# 📚 Recommendation Logic
def get_recommendations(user_query, top_n=5):
    user_embedding = model.encode([user_query], convert_to_numpy=True, device='cpu')
    similarities = cosine_similarity(user_embedding, book_embeddings)[0]
    similar_books_idx = np.argsort(similarities)[-top_n:][::-1]

    recommendations = []
    for idx in similar_books_idx:
        book = {
            "title": str(df.iloc[idx].get('Title', 'Unknown')),
            "author": str(df.iloc[idx].get('Authors', 'Unknown')),
            "category": str(df.iloc[idx].get('Category', 'N/A')),
            "year": int(df.iloc[idx].get('Publish Date (Year)', 0)) if not pd.isna(df.iloc[idx].get('Publish Date (Year)')) else None,
            "relevance": round(float(similarities[idx]), 2)
        }
        recommendations.append(book)

    return recommendations


# 🔌 API Route
@app.route("/recommend", methods=["POST"])
def recommend():
    data = request.get_json()
    user_query = data.get("query")
    top_n = int(data.get("top_n", 5))

    if not user_query:
        return jsonify({"error": "Query is required."}), 400

    try:
        results = get_recommendations(user_query, top_n)
        return jsonify({"query": user_query, "results": results})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

# Health check
@app.route("/", methods=["GET"])
def index():
    return "📘 Low-Memory Book Recommendation API is running."

# ▶️ Run server
if __name__ == "__main__":
    app.run(debug=False)
