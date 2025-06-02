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

    // Prevent clicks on search results except for the create post button
    resultContainer.addEventListener('click', function(e) {
        // Only allow clicks on the create-post-btn
        if (!e.target.classList.contains('create-post-btn')) {
            e.preventDefault();
            // If the clicked element is a child of create-post-btn, find the button and click it
            const createPostBtn = e.target.closest('.create-post-btn');
            if (createPostBtn) {
                createPostBtn.click();
            }
            return false;
        }
    });

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

                    const resultItem = document.createElement("div");
                    resultItem.classList.add("search-result");
                    resultItem.style.cursor = "default";
                    
                    resultItem.innerHTML = `
                        <div class="crypto-result" style="cursor: default;">
                            <img src="${logoUrls[0]}" alt="${coin.name} Logo" class="crypto-logo" style="cursor: default;"
                                 onerror="this.onerror=null;this.src='${logoUrls[1]}';"/>
                            <div class="crypto-info" style="cursor: default;">
                                <h3 style="cursor: default;">${coin.name} <span class="crypto-symbol">${coin.symbol}</span></h3>
                                <p class="crypto-price" style="cursor: default;">$${parseFloat(coin.price_usd).toLocaleString('sv-SE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                                <p class="${priceChangeClass}" style="cursor: default;">${priceChangeSymbol} ${priceChange.toFixed(2)}%</p>
                            </div>
                            <div class="crypto-market-info" style="cursor: default;">
                                <p style="cursor: default;">Rank: #${coin.rank}</p>
                                <p style="cursor: default;">Volym 24h: $${parseInt(coin.volume24).toLocaleString('sv-SE')}</p>
                            </div>
                            <a href="discussions.php?coin_id=${coin.id}" class="create-post-btn">
                                📝 Skapa inlägg
                            </a>
                        </div>
                    `;
                    
                    resultContainer.appendChild(resultItem);
                });
            } else {
                statusContainer.innerHTML = "❌ Inga resultat hittades";
                resultContainer.innerHTML = `
                    <div class="no-results">
                        <p>Inga kryptovalutor matchade din sökning.</p>
                        <p>Försök med ett annat namn eller symbol.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error("Error:", error);
            statusContainer.innerHTML = "⚠ Ett fel uppstod vid sökningen";
            resultContainer.innerHTML = `
                <div class="error-message">
                    <p>Kunde inte hämta kryptovalutor.</p>
                    <p>Vänligen försök igen senare.</p>
                </div>
            `;
        }
    }

    // Search on input with debounce
    searchInput.addEventListener("input", function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        // Don't search if query hasn't changed
        if (query === lastQuery) return;
        lastQuery = query;
        
        searchTimeout = setTimeout(() => {
            fetchCryptoData(query);
        }, 300);
    });

    // Search on button click
    searchButton.addEventListener("click", function() {
        const query = searchInput.value.trim();
        if (query === lastQuery) return;
        lastQuery = query;
        fetchCryptoData(query);
    });
});
