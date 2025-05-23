async function fetchCryptoData(symbol) {
    const response = await fetch(`https://api.coingecko.com/api/v3/simple/price?ids=${symbol}&vs_currencies=usd`);
    const data = await response.json();
    document.getElementById("crypto-price").innerText = `Pris: ${data[symbol].usd} USD`;
}
//Tja Hej Salam/Salle
