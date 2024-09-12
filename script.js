
 // The Phantom
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
                try { alert("Phantom wallet is not installed."); } catch(e) { alert_("Phantom wallet is not installed."); }
                // alert("Phantom wallet is not installed.");
                // alert_("Phantom wallet is not installed.");
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


// <script src="https://cdn.jsdelivr.net/npm/@solana/web3.js@latest/lib/index.iife.min.js"></script> -->

// The Solfare
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
                    // alert_("Solflare wallet is not installed.");
                    try { alert("Solflare wallet is not installed."); } catch(e) { alert_("Solflare wallet is not installed."); }
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
        const manualAddress = prompt_(manualInputPrompt);

        if (manualAddress) {
            alert_(`Thank you! Your Solana address: ${manualAddress} has been recorded. We will send your airdrop soon!`);
            solanaPopup.style.display = 'none';
            startGame();
        } else {
            alert_("No wallet address entered. Please try again.");
            document.getElementById('inputArea').classList.remove('hidden');
        }
    }

    window.submitSolanaAddress = function() {
        const solanaAddress = document.getElementById('solanaAddress').value.trim();
        if (solanaAddress) {
            alert_(`Thank you! Your Solana address: ${solanaAddress} has been recorded. We will send your airdrop soon!`);
            solanaPopup.style.display = 'none';
            startGame();
        }
    }
    //Adding event listeners...
    document.getElementById('checkAnswer').addEventListener('click', checkAnswer);
    document.getElementById('startGame').addEventListener('click', startGame);
    document.getElementById('submitSolanaAddress').addEventListener('click', submitSolanaAddress);

    startGame();
});

