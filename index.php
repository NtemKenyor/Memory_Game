<?php
// Generate a random nonce
$nonce = base64_encode(random_bytes(16));

// Set the Content Security Policy header to allow inline and external scripts/styles
header("Content-Security-Policy: 
  script-src 'self' 'nonce-$nonce' https://example.com; 
  style-src 'self' 'nonce-$nonce' https://example.com; 
  object-src 'none'; 
  frame-ancestors 'none';");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Game</title>
    <!-- <link rel="stylesheet" href="styles.css"> -->

    <!-- DSCVR Canvas Meta Tags -->
    <meta name="dscvr:canvas:version" content="vNext">
    <meta name="og:image" content="https://roynek.com/Memory_Game/memory_game.png">
    
    <style nonce="<?php echo $nonce; ?>">
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(-45deg, #ff6f61, #d83c7d, #ffb100, #61dafb);
            background-size: 400% 400%;
            animation: gradientAnimation 10s ease infinite;
            color: white;
        }

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .container {
            text-align: center;
            position: relative;
        }

        canvas {
            background-color: #ffffff;
            margin-bottom: 20px;
            border: 2px solid #ccc;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.5);
        }

        #inputArea,
        #messageArea {
            margin-top: 20px;
        }

        #userInput {
            padding: 10px;
        }

        .hidden {
            display: none;
        }

        button {
            padding: 10px 20px;
            background-color: #61dafb;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #21a1f1;
        }

        #timer, #score {
            position: absolute;
            top: 10px;
            font-size: 18px;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 10px;
            border-radius: 5px;
        }

        #timer {
            right: 20px;
        }

        #score {
            right: 140px;
        }

        #solanaPopup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            z-index: 1000;
            box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.7);
        }

        #solanaPopup input {
            padding: 10px;
            width: 80%;
            margin: 10px 0;
        }

        #solanaPopup button {
            padding: 10px 20px;
            background-color: #61dafb;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        #solanaPopup button:hover {
            background-color: #21a1f1;
        }
    </style>
    <!-- Include Solana Wallet Adapter -->
<script src="https://cdn.jsdelivr.net/npm/@solana/wallet-adapter@0.9.0/dist/index.umd.js"></script>

<!-- Solana web3.js -->
<script src="https://cdn.jsdelivr.net/npm/@solana/web3.js@latest/lib/index.iife.min.js"></script>

<script nonce="<?php echo $nonce; ?>"> // The Phantom
    // Custom Phantom Wallet Adapter
    class PhantomWalletAdapter {
        constructor() {
            this.connected = false;
            this.publicKey = null;
        }

        async connect() {
            if (window.solana && window.solana.isPhantom) {
                await window.solana.connect();
                this.publicKey = window.solana.publicKey;
                this.connected = true;
            } else {
                alert("Phantom wallet is not installed.");
            }
        }

        disconnect() {
            if (window.solana && this.connected) {
                window.solana.disconnect();
                this.connected = false;
                this.publicKey = null;
            }
        }
    }

    // Now you can use PhantomWalletAdapter in your script
</script>

<!-- Include Solana Web3.js -->
<script src="https://cdn.jsdelivr.net/npm/@solana/web3.js@latest/lib/index.iife.min.js"></script>

<!-- Custom Solflare Wallet Adapter -->
<script nonce="<?php echo $nonce; ?>"> // The Solfare
    class SolflareWalletAdapter {
        constructor() {
            this.connected = false;
            this.publicKey = null;
        }

        async connect() {
            try {
                const provider = window.solflare;
                if (provider) {
                    await provider.connect();
                    this.publicKey = provider.publicKey.toString();
                    this.connected = true;
                } else {
                    alert("Solflare wallet is not installed.");
                }
            } catch (err) {
                console.error(err);
                this.connected = false;
            }
        }

        disconnect() {
            if (this.connected) {
                window.solflare.disconnect();
                this.connected = false;
                this.publicKey = null;
            }
        }
    }

    // Function to initiate wallet connection
    async function connectWallet() {
        const wallet = new SolflareWalletAdapter();
        await wallet.connect();

        if (wallet.connected) {
            return wallet.publicKey;
        } else {
            return null;
        }
    }
</script>


<script>

// const wallet = new SolflareWalletAdapter();

// wallet.connect()
//     .then(() => {
//         if (wallet.connected && wallet.publicKey) {
//             const publicKey = wallet.publicKey.toString();
//             showSolanaPopup(publicKey);
//         } else {
//             alert("Failed to retrieve the public key. Please enter your wallet address manually.");
//             requestManualWalletInput();
//         }
//     })
//     .catch(err => {
//         alert("Failed to connect wallet. Please try again.");
//         console.error(err);
//         requestManualWalletInput(); // Fallback to manual input
//     });

    document.addEventListener('DOMContentLoaded', () => {
    // const { WalletAdapterNetwork, Connection, clusterApiUrl, PublicKey } = solanaWalletAdapter;
    // const { PhantomWalletAdapter } = solanaWalletAdapter.wallets;

    let wallet;
    let publicKey;

    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');
    canvas.width = 500;
    canvas.height = 150;

    const words = [
        "apple", "banana", "cherry", "date", "elderberry",
        "fig", "grape", "honeydew", "kiwi", "lemon",
        "mango", "nectarine", "orange", "papaya", "quince"
    ];
    let level = 1;
    let score = 0;
    let currentWords = [];
    let timer;
    let countdown;

    const timerDisplay = document.getElementById('timer');
    const scoreDisplay = document.getElementById('score');
    const solanaPopup = document.getElementById('solanaPopup');

    window.startGame = function() {
        level = 1;
        score = 0;
        updateScore();
        removeMessage();
        showWords();
    }

    function showWords() {
        clearCanvas();
        currentWords = generateWords(level);
        ctx.font = '30px Arial';
        ctx.fillStyle = 'black';
        ctx.textAlign = 'center';
        ctx.fillText(currentWords.join(' '), canvas.width / 2, canvas.height / 2);
        startTimer(7, clearWords);
    }

    function generateWords(count) {
        let selectedWords = [];
        for (let i = 0; i < count; i++) {
            const randomWord = words[Math.floor(Math.random() * words.length)];
            selectedWords.push(randomWord);
        }
        return selectedWords;
    }

    function clearWords() {
        clearCanvas();
        document.getElementById('inputArea').classList.remove('hidden');
    }

    function clearCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    // window.checkAnswer = function() {
    //     const userInput = document.getElementById('userInput').value.trim().toLowerCase().split(' ');
    //     document.getElementById('inputArea').classList.add('hidden');
    //     const lowerCaseCurrentWords = currentWords.map(word => word.toLowerCase());
        
    //     if (arraysEqual(userInput, lowerCaseCurrentWords)) {
    //         level++;
    //         score += 10;
    //         updateScore();
    //         if (level <= 30) {
    //             showWords();
    //         } else {
    //             showMessage("🎉 Congratulations! You won the game!", true);
    //         }
    //     } else {
    //         connectWallet();
    //     }
    // }

    function arraysEqual(arr1, arr2) {
        if (arr1.length !== arr2.length) return false;
        for (let i = 0; i < arr1.length; i++) {
            if (arr1[i] !== arr2[i]) return false;
        }
        return true;
    }

    function showMessage(msg, isWin) {
        const messageArea = document.getElementById('messageArea');
        const message = document.getElementById('message');
        message.innerHTML = msg;
        messageArea.classList.remove('hidden');
    }

    function removeMessage(){
        const messageArea = document.getElementById('messageArea');
        messageArea.classList.add('hidden');
    }

    function startTimer(seconds, callback) {
        clearInterval(countdown);
        let timeLeft = seconds;
        timerDisplay.textContent = `Time: ${timeLeft}s`;

        countdown = setInterval(() => {
            timeLeft--;
            timerDisplay.textContent = `Time: ${timeLeft}s`;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                callback();
            }
        }, 1000);
    }

    function updateScore() {
        scoreDisplay.textContent = `Score: ${score}`;
    }


    window.checkAnswer = async function() {
        const userInput = document.getElementById('userInput').value.trim().toLowerCase().split(' ');
        document.getElementById('inputArea').classList.add('hidden');
        const lowerCaseCurrentWords = currentWords.map(word => word.toLowerCase());
        
        if (arraysEqual(userInput, lowerCaseCurrentWords)) {
            level++;
            score += 10;
            updateScore();
            if (level <= 30) {
                showWords();
            } else {
                showMessage("🎉 Congratulations! You won the game!", true);
            }
        } else {
            const publicKey = await connectWallet();

            if (publicKey) {
                showSolanaPopup(publicKey);
            } else {
                requestManualWalletInput();
            }
        }
    }

    // function connectWallet() {
    //     const wallet = new SolflareWalletAdapter();
    //     wallet.connect()
    //         .then(() => {
    //             if (wallet.connected && wallet.publicKey) {
    //                 const publicKey = wallet.publicKey.toString();
    //                 showSolanaPopup(publicKey);
    //             } else {
    //                 alert("Failed to retrieve the public key. Please enter your wallet address manually.");
    //                 requestManualWalletInput();
    //             }
    //         })
    //         .catch(err => {
    //             alert("Failed to connect wallet. Please try again.");
    //             console.error(err);
    //             requestManualWalletInput(); // Fallback to manual input
    //         });
    // }

    function showSolanaPopup(publicKey) {
        solanaPopup.style.display = 'block';
        const solanaAddressInput = document.getElementById('solanaAddress');
        solanaAddressInput.value = publicKey;
    }

    function requestManualWalletInput() {
        const manualInputPrompt = "We couldn't connect to your wallet. Please enter your Solana wallet address manually:";
        const manualAddress = prompt(manualInputPrompt);

        if (manualAddress) {
            alert(`Thank you! Your Solana address: ${manualAddress} has been recorded. We will send your airdrop soon!`);
            solanaPopup.style.display = 'none';
            startGame();
        } else {
            alert("No wallet address entered. Please try again.");
            document.getElementById('inputArea').classList.remove('hidden');
        }
    }

    window.submitSolanaAddress = function() {
        const solanaAddress = document.getElementById('solanaAddress').value.trim();
        if (solanaAddress) {
            alert(`Thank you! Your Solana address: ${solanaAddress} has been recorded. We will send your airdrop soon!`);
            solanaPopup.style.display = 'none';
            startGame();
        }
    }

    startGame();
});

</script>
</head>
<body>
    <div class="container">
        <div id="timer">Time: 7s</div>
        <div id="score">Score: 0</div>
        <canvas id="gameCanvas"></canvas>
        <div id="inputArea" class="hidden">
            <input type="text" id="userInput" placeholder="Enter the words you have just seen...">
            <button onclick="checkAnswer()">Submit</button>
        </div>
        <div id="messageArea" class="hidden">
            <p id="message"></p>
            <button onclick="startGame()">Try Again</button>
        </div>
        <div id="solanaPopup">
            <p>❌ You have failed, but don't worry!</p>
            <p>Enter your Solana address to receive some tokens as encouragement to keep playing.</p>
            <input type="text" id="solanaAddress" placeholder="Enter your Solana address...">
            <button onclick="submitSolanaAddress()">Submit</button>
        </div>
    </div>
</body>
</html>
