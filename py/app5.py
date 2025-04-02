from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity
from flask_cors import CORS 

app = Flask(__name__)
CORS(app)  

# Load dataset and model once to optimize performance
dataset_path = "BooksDatasetCleanFiltered.csv"
df = pd.read_csv(dataset_path)
model = SentenceTransformer('all-MiniLM-L6-v2')

# Ensure necessary columns exist and create combined text field
expected_columns = ['Title', 'Authors', 'Description', 'Category', 'Publish Date (Year)']
available_columns = [col for col in expected_columns if col in df.columns]
df = df.dropna(subset=['Title'])
df['combined_text'] = df[['Title', 'Authors', 'Description', 'Category']].apply(lambda row: ' '.join(row.values.astype(str)), axis=1)

# Encode books once for efficiency
book_embeddings = model.encode(df['combined_text'].tolist(), show_progress_bar=True)

@app.route('/recommend', methods=['POST'])
def recommend_books():
    data = request.get_json()
    user_query = data.get("query", "")
    top_n = data.get("top_n", 5)

    if not user_query:
        return jsonify({"error": "Query cannot be empty"}), 400

    # Encode user query
    user_embedding = model.encode([user_query])
    similarities = cosine_similarity(user_embedding, book_embeddings)[0]
    similar_books_idx = np.argsort(similarities)[-top_n:][::-1]

    recommendations = []
    for idx in similar_books_idx:
        book_data = {
            "Title": df.iloc[idx]['Title'],
            "Author": df.iloc[idx]['Authors'],
            "Category": df.iloc[idx]['Category'],
            "Year": df.iloc[idx]['Publish Date (Year)'],
            "Similarity": round(float(similarities[idx]), 2)
        }
        recommendations.append(book_data)

    return jsonify(recommendations)

if __name__ == '__main__':
    app.run(debug=True)
