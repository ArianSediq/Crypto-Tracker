document.addEventListener("DOMContentLoaded", function () {
    const API_URL = "https://api.coinlore.net/api/tickers/?limit=100";
    const searchInput = document.getElementById("crypto-symbol");
    const searchButton = document.getElementById("search-button");
    const resultContainer = document.getElementById("crypto-price");
    const statusContainer = document.getElementById("search-status");
    let searchTimeout;
    let lastQuery = '';

    // Initial data fetch
    fetchCryptoData('');

    async function fetchCryptoData(query) {
        try {
            // Don't show loading for empty queries
            if (query) {
                statusContainer.innerHTML = "🔍 Söker...";
            }

            const CACHE_KEY = "cryptoData";
            const CACHE_TIMESTAMP_KEY = "cryptoDataTimestamp";
            let cachedData = sessionStorage.getItem(CACHE_KEY);
            let timestamp = sessionStorage.getItem(CACHE_TIMESTAMP_KEY);
            const now = Date.now();
            const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

            let data;
            // Check if cache is valid
            if (cachedData && timestamp && (now - parseInt(timestamp) < CACHE_DURATION)) {
                console.log("🔄 Using cached data");
                data = JSON.parse(cachedData);
            } else {
                console.log("🌐 Fetching new API data...");
                const response = await fetch(API_URL);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                data = await response.json();
                sessionStorage.setItem(CACHE_KEY, JSON.stringify(data));
                sessionStorage.setItem(CACHE_TIMESTAMP_KEY, now.toString());
            }

            // Find all matching cryptocurrencies
            const matches = query ? data.data.filter(coin =>
                coin.name.toLowerCase().includes(query.toLowerCase()) ||
                coin.symbol.toLowerCase().includes(query.toLowerCase())
            ) : data.data.slice(0, 10); // Show top 10 if no query

            resultContainer.innerHTML = ""; // Clear previous results

            if (matches.length > 0) {
                if (query) {
                    statusContainer.innerHTML = `✅ Hittade ${matches.length} resultat`;
                } else {
                    statusContainer.innerHTML = "Topplista Kryptovalutor";
                }
                
                matches.forEach(coin => {
                    const priceChange = parseFloat(coin.percent_change_24h);
                    const priceChangeClass = priceChange >= 0 ? 'positive-change' : 'negative-change';
                    const priceChangeSymbol = priceChange >= 0 ? '↗' : '↘';
                    
                    // Try multiple logo sources
                    const logoUrls = [
                        `https://static.coincap.io/assets/icons/${coin.symbol.toLowerCase()}@2x.png`,
                        `https://assets.coincap.io/assets/icons/${coin.symbol.toLowerCase()}@2x.png`,
                        `https://cryptoicons.org/api/icon/${coin.symbol.toLowerCase()}/200`
                    ];

                    const resultItem = document.createElement("a");
                    resultItem.classList.add("search-result");
                    resultItem.href = `coin_info.php?id=${coin.id}`;
                    
                    resultItem.innerHTML = `
                        <div class="crypto-result">
                            <img src="${logoUrls[0]}" 
                                 alt="${coin.name}" 
                                 class="crypto-logo"
                                 onerror="this.onerror=null; this.src='${logoUrls[1]}'; this.onerror=null;">
                            <div class="crypto-info">
                                <h3>${coin.name} <span class="crypto-symbol">${coin.symbol}</span></h3>
                                <p class="crypto-price">$${parseFloat(coin.price_usd).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 6 })}</p>
                                <p class="crypto-change ${priceChangeClass}">${priceChangeSymbol} ${Math.abs(priceChange).toFixed(2)}% (24h)</p>
                            </div>
                            <div class="crypto-market-info">
                                <p>Rank: #${coin.rank}</p>
                                <p>Market Cap: $${parseFloat(coin.market_cap_usd).toLocaleString('en-US', { maximumFractionDigits: 0 })}</p>
                            </div>
                        </div>
                    `;

                    resultContainer.appendChild(resultItem);
                });
            } else {
                statusContainer.innerHTML = "❌ Inga kryptovalutor hittades!";
                resultContainer.innerHTML = `
                    <div class="no-results">
                        <p>Inga resultat hittades för "${query}"</p>
                        <p>Försök med ett annat namn eller symbol</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error:', error);
            statusContainer.innerHTML = "❌ Ett fel uppstod vid sökningen";
            resultContainer.innerHTML = `
                <div class="error-message">
                    <p>Kunde inte hämta kryptovalutor</p>
                    <p>Var god försök igen senare</p>
                </div>
            `;
        }
    }

    // Real-time search with debouncing
    searchInput.addEventListener("input", function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        // Don't search if the query hasn't changed
        if (query === lastQuery) return;
        lastQuery = query;
        
        if (query.length >= 1) {
            searchTimeout = setTimeout(() => {
                fetchCryptoData(query);
            }, 300); // Wait 300ms after user stops typing
        } else {
            // Show initial top 10 when search is cleared
            fetchCryptoData('');
        }
    });

    // Button click search
    searchButton.addEventListener("click", function () {
        const query = searchInput.value.trim();
        if (query.length > 0) {
            fetchCryptoData(query);
        } else {
            fetchCryptoData('');
        }
    });

    // Allow search with Enter key
    searchInput.addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            const query = this.value.trim();
            fetchCryptoData(query);
        }
    });
});
