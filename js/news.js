document.addEventListener("DOMContentLoaded", function () {
    const newsContainer = document.getElementById("crypto-news");

    fetch("/Crypto-Tracker/api/fetch_news.php")
        .then(response => response.json())
        .then(news => {
            newsContainer.innerHTML = ""; // Clear previous results

            news.slice(0, 5).forEach(article => { // Show top 5 news items
                const newsItem = document.createElement("div");
                newsItem.classList.add("news-item");

                // Handle missing fields
                const title = article.title || "No Title Available";
                const description = article.description || "No Description Available";
                const link = article.link || "#";

                newsItem.innerHTML = `
                <h3>${title}</h3>
                <p><strong>📅 Publicerad:</strong> ${new Date(article.pubDate).toLocaleDateString()}</p>
                <p>${description}</p>
                <a href="${link}" target="_blank">🔗 Läs mer</a>
            `;

                newsContainer.appendChild(newsItem);
            });
        })
        .catch(error => {
            console.error("Error fetching news:", error);
            newsContainer.innerHTML = "<p>❌ Kunde inte ladda nyheter!</p>";
        });
});

