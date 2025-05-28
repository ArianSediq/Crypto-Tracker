document.addEventListener("DOMContentLoaded", function () {
    const API_URL = "https://api.coinlore.net/api/tickers/?limit=100"; // Get top 100 cryptos
    const searchInput = document.getElementById("crypto-symbol");
    const searchButton = document.getElementById("search-button");
    const resultContainer = document.getElementById("crypto-price");

    async function fetchCryptoData(query) {
        const CACHE_KEY = "cryptoData"; // Cache API response
        let cachedData = sessionStorage.getItem(CACHE_KEY);

        if (cachedData) {
            console.log("🔄 Using cached data");
            cachedData = JSON.parse(cachedData);
        } else {
            console.log("🌐 Fetching new API data...");
            const response = await fetch("https://api.coinlore.net/api/tickers/?limit=100");
            cachedData = await response.json();
            sessionStorage.setItem(CACHE_KEY, JSON.stringify(cachedData)); // Store in cache
        }

        // Find all matching cryptocurrencies based on name or symbol
        const matches = cachedData.data.filter(coin =>
            coin.name.toLowerCase().includes(query.toLowerCase()) ||
            coin.symbol.toLowerCase().includes(query.toLowerCase())
        );

        const resultContainer = document.getElementById("crypto-price");
        resultContainer.innerHTML = ""; // Clear previous results

        if (matches.length > 0) {
            matches.forEach(coin => { // Loop through ALL results
                const logoUrl = `https://static.coincap.io/assets/icons/${coin.symbol.toLowerCase()}@2x.png`;

                // Create a new div for each result
                const resultItem = document.createElement("div");
                resultItem.classList.add("search-result");
                resultItem.innerHTML = `
                <img src="${logoUrl}" alt="${coin.name} Logo" class="crypto-logo">
                <p><strong>${coin.name} (${coin.symbol}):</strong> $${coin.price_usd}</p>
            `;

                // Append results dynamically
                resultContainer.appendChild(resultItem);
            });
        } else {
            resultContainer.innerHTML = "❌ Inga kryptovalutor hittades!";
        }
    }






    // Bind search event to button click
    searchButton.addEventListener("click", function () {
        const query = searchInput.value.trim();
        if (query.length > 0) {
            fetchCryptoData(query);
        } else {
            resultContainer.innerHTML = "❌ Ange en kryptovaluta att söka efter!";
        }
    });
});
