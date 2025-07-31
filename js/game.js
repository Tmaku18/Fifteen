// Sliding Puzzle Game Logic
class SlidingPuzzle {
    constructor() {
        this.boardSize = 4;
        this.tileSize = 100;
        this.tiles = [];
        this.emptyPosition = { row: 3, col: 3 };
        this.moves = 0;
        this.startTime = null;
        this.timerInterval = null;
        this.isGameActive = false;
        this.currentBackground = window.gameData.currentBackground;

        this.initializeElements();
        this.selectRandomBackground();
        this.createBoard();
        this.loadLeaderboard();
        this.setupEventListeners();
        this.addLeaderboardRefreshButton();
    }
    
    initializeElements() {
        this.board = document.getElementById('puzzle-board');
        this.moveCounter = document.getElementById('move-count');
        this.timer = document.getElementById('timer');
        this.shuffleBtn = document.getElementById('shuffle-btn');
        this.newGameBtn = document.getElementById('new-game-btn');
        this.backgroundSelect = document.getElementById('background-select');
        this.gameStatus = document.getElementById('game-status');
        this.winModal = document.getElementById('win-modal');
        this.historyModal = document.getElementById('history-modal');
    }
    
    createBoard() {
        this.board.innerHTML = '';
        this.tiles = [];
        
        let tileNumber = 1;
        for (let row = 0; row < this.boardSize; row++) {
            for (let col = 0; col < this.boardSize; col++) {
                if (row === 3 && col === 3) continue; // Empty space
                
                const tile = document.createElement('div');
                tile.className = 'puzzle-tile';
                tile.textContent = tileNumber;
                tile.dataset.number = tileNumber;
                tile.dataset.originalRow = row;
                tile.dataset.originalCol = col;
                
                this.positionTile(tile, row, col);
                this.setTileBackground(tile, row, col);
                
                tile.addEventListener('click', () => this.handleTileClick(tile));
                tile.addEventListener('mouseenter', () => this.handleTileHover(tile));
                tile.addEventListener('mouseleave', () => this.handleTileLeave(tile));
                
                this.board.appendChild(tile);
                this.tiles.push(tile);
                tileNumber++;
            }
        }
        
        this.updateMovableTiles();
    }
    
    positionTile(tile, row, col) {
        tile.style.left = `${col * this.tileSize}px`;
        tile.style.top = `${row * this.tileSize}px`;
        tile.dataset.currentRow = row;
        tile.dataset.currentCol = col;
    }
    
    setTileBackground(tile, originalRow, originalCol) {
        const backgroundX = -originalCol * this.tileSize;
        const backgroundY = -originalRow * this.tileSize;
        tile.style.backgroundImage = `url('images/${this.currentBackground}')`;
        tile.style.backgroundPosition = `${backgroundX}px ${backgroundY}px`;
    }
    
    handleTileClick(tile) {
        if (!this.isTileMovable(tile)) return;
        
        if (!this.isGameActive) {
            this.startGame();
        }
        
        this.moveTile(tile);
        this.updateMovableTiles();
        
        if (this.checkWin()) {
            this.endGame();
        }
    }
    
    handleTileHover(tile) {
        if (this.isTileMovable(tile)) {
            tile.classList.add('movable');
        }
    }
    
    handleTileLeave(tile) {
        tile.classList.remove('movable');
    }
    
    isTileMovable(tile) {
        const row = parseInt(tile.dataset.currentRow);
        const col = parseInt(tile.dataset.currentCol);
        const emptyRow = this.emptyPosition.row;
        const emptyCol = this.emptyPosition.col;
        
        const rowDiff = Math.abs(row - emptyRow);
        const colDiff = Math.abs(col - emptyCol);
        
        return (rowDiff === 1 && colDiff === 0) || (rowDiff === 0 && colDiff === 1);
    }
    
    moveTile(tile) {
        const tileRow = parseInt(tile.dataset.currentRow);
        const tileCol = parseInt(tile.dataset.currentCol);
        
        // Move tile to empty position
        this.positionTile(tile, this.emptyPosition.row, this.emptyPosition.col);
        
        // Update empty position
        this.emptyPosition.row = tileRow;
        this.emptyPosition.col = tileCol;
        
        // Update move counter
        this.moves++;
        this.moveCounter.textContent = this.moves;
    }
    
    updateMovableTiles() {
        this.tiles.forEach(tile => {
            tile.classList.remove('movable');
        });
    }
    
    shuffle() {
        this.resetGame();
        
        // Perform random valid moves to ensure solvable puzzle
        const shuffleMoves = 200;
        for (let i = 0; i < shuffleMoves; i++) {
            const movableTiles = this.tiles.filter(tile => this.isTileMovable(tile));
            if (movableTiles.length > 0) {
                const randomTile = movableTiles[Math.floor(Math.random() * movableTiles.length)];
                const currentMoves = this.moves;
                this.moveTile(randomTile);
                this.moves = currentMoves; // Don't count shuffle moves
            }
        }
        
        this.moveCounter.textContent = '0';
        this.updateMovableTiles();
        this.gameStatus.innerHTML = '<p>Game shuffled! Click any movable tile to start.</p>';
    }
    
    checkWin() {
        // Check if empty space is in bottom-right corner
        if (this.emptyPosition.row !== 3 || this.emptyPosition.col !== 3) {
            return false;
        }
        
        // Check if all tiles are in correct positions
        return this.tiles.every(tile => {
            const currentRow = parseInt(tile.dataset.currentRow);
            const currentCol = parseInt(tile.dataset.currentCol);
            const originalRow = parseInt(tile.dataset.originalRow);
            const originalCol = parseInt(tile.dataset.originalCol);
            
            return currentRow === originalRow && currentCol === originalCol;
        });
    }
    
    startGame() {
        if (this.isGameActive) return;
        
        this.isGameActive = true;
        this.startTime = Date.now();
        this.gameStatus.innerHTML = '<p>Game in progress... Good luck!</p>';
        
        this.timerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            this.timer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }, 1000);
    }
    
    endGame() {
        this.isGameActive = false;
        clearInterval(this.timerInterval);

        const totalTime = Math.floor((Date.now() - this.startTime) / 1000);

        console.log('Game completed!', {
            moves: this.moves,
            totalTime: totalTime,
            background: this.currentBackground
        });

        // Add celebration effects
        this.celebrateWin();

        // Show win modal with delay for effect
        setTimeout(() => {
            document.getElementById('final-time').textContent = this.timer.textContent;
            document.getElementById('final-moves').textContent = this.moves;
            this.winModal.style.display = 'flex';
        }, 1500);

        // Save game stats immediately
        this.saveGameStats(totalTime);

        this.gameStatus.innerHTML = '<p>🎉 Congratulations! You solved the puzzle!</p>';
    }
    
    resetGame() {
        this.isGameActive = false;
        clearInterval(this.timerInterval);
        this.moves = 0;
        this.startTime = null;
        this.moveCounter.textContent = '0';
        this.timer.textContent = '00:00';
        this.emptyPosition = { row: 3, col: 3 };
        this.createBoard();
        this.gameStatus.innerHTML = '<p>Click "Shuffle" to start a new game!</p>';
    }
    
    changeBackground(newBackground) {
        this.currentBackground = newBackground;
        this.tiles.forEach(tile => {
            const originalRow = parseInt(tile.dataset.originalRow);
            const originalCol = parseInt(tile.dataset.originalCol);
            this.setTileBackground(tile, originalRow, originalCol);
        });
        
        // Save preference
        this.saveUserPreference(newBackground);
    }
    
    setupEventListeners() {
        this.shuffleBtn.addEventListener('click', () => this.shuffle());
        this.newGameBtn.addEventListener('click', () => this.resetGame());
        this.backgroundSelect.addEventListener('change', (e) => this.changeBackground(e.target.value));
        
        // Modal event listeners
        document.getElementById('play-again-btn').addEventListener('click', () => {
            this.winModal.style.display = 'none';
            this.shuffle();
        });
        
        document.getElementById('close-modal-btn').addEventListener('click', () => {
            this.winModal.style.display = 'none';
        });
        
        document.getElementById('view-history-btn').addEventListener('click', () => {
            this.showUserHistory();
        });
        
        // Close modals when clicking outside
        this.winModal.addEventListener('click', (e) => {
            if (e.target === this.winModal) {
                this.winModal.style.display = 'none';
            }
        });
        
        this.historyModal.addEventListener('click', (e) => {
            if (e.target === this.historyModal) {
                this.historyModal.style.display = 'none';
            }
        });
    }
    
    async saveGameStats(timeSeconds) {
        try {
            console.log('Saving game stats:', {
                moves: this.moves,
                time_seconds: timeSeconds,
                background_image: this.currentBackground
            });

            const response = await fetch('api/save_game.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    moves: this.moves,
                    time_seconds: timeSeconds,
                    background_image: this.currentBackground
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('Game stats saved successfully:', result);

            // Refresh leaderboard after successful save
            this.loadLeaderboard();

        } catch (error) {
            console.error('Error saving game stats:', error);
            // Show user-friendly message
            const gameStatus = document.querySelector('.game-status');
            if (gameStatus) {
                gameStatus.innerHTML += '<br><small style="color: orange;">⚠️ Score may not have been saved to leaderboard</small>';
            }
        }
    }
    
    async saveUserPreference(background) {
        try {
            await fetch('api/save_preferences.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    background: background
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            if (result.error) {
                console.error('Error saving preferences:', result.error);
            }
        } catch (error) {
            console.error('Error saving preferences:', error);
            // Don't show user error for preferences - it's not critical
        }
    }
    
    async loadLeaderboard() {
        try {
            const response = await fetch('api/get_leaderboard.php');

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            const leaderboard = document.getElementById('leaderboard');
            leaderboard.innerHTML = '';

            if (data.error) {
                leaderboard.innerHTML = `<div class="error-message">Error: ${data.error}</div>`;
                return;
            }

            if (!data || data.length === 0) {
                leaderboard.innerHTML = '<div class="no-data">No games played yet. Be the first to complete a puzzle!</div>';
                return;
            }

            data.forEach((entry, index) => {
                const item = document.createElement('div');
                item.className = 'leaderboard-item';

                const minutes = Math.floor(entry.time_seconds / 60);
                const seconds = entry.time_seconds % 60;
                const timeStr = `${minutes}:${seconds.toString().padStart(2, '0')}`;

                item.innerHTML = `
                    <span class="leaderboard-rank">#${index + 1}</span>
                    <span class="leaderboard-name">${entry.username}</span>
                    <div class="leaderboard-stats">
                        <span>${timeStr}</span>
                        <span>${entry.moves} moves</span>
                    </div>
                `;

                leaderboard.appendChild(item);
            });
        } catch (error) {
            console.error('Error loading leaderboard:', error);
            const leaderboard = document.getElementById('leaderboard');
            leaderboard.innerHTML = `<div class="error-message">Failed to load leaderboard. Please refresh the page.</div>`;
        }
    }
    
    async showUserHistory() {
        try {
            const response = await fetch('api/get_user_history.php');

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            const historyContainer = document.getElementById('user-history');
            historyContainer.innerHTML = '';

            if (data.error) {
                historyContainer.innerHTML = `<div class="error-message">Error: ${data.error}</div>`;
                this.historyModal.style.display = 'flex';
                return;
            }

            if (!data || data.length === 0) {
                historyContainer.innerHTML = '<div class="no-data">No games played yet. Complete a puzzle to see your history!</div>';
            } else {
                data.forEach((game, index) => {
                    const item = document.createElement('div');
                    item.className = 'history-item';

                    const minutes = Math.floor(game.time_seconds / 60);
                    const seconds = game.time_seconds % 60;
                    const timeStr = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    const date = new Date(game.completed_at).toLocaleDateString();

                    item.innerHTML = `
                        <div class="history-game">
                            <span class="history-rank">#${index + 1}</span>
                            <span class="history-time">${timeStr}</span>
                            <span class="history-moves">${game.moves} moves</span>
                            <span class="history-date">${date}</span>
                        </div>
                    `;

                    historyContainer.appendChild(item);
                });
            }

            this.historyModal.style.display = 'flex';
        } catch (error) {
            console.error('Error loading user history:', error);
            const historyContainer = document.getElementById('user-history');
            historyContainer.innerHTML = `<div class="error-message">Failed to load game history. Please try again.</div>`;
            this.historyModal.style.display = 'flex';
        }
    }

    celebrateWin() {
        // Add celebration class to game board
        this.board.classList.add('celebration');

        // Create confetti effect
        this.createConfetti();

        // Add pulsing effect to tiles
        this.tiles.forEach((tile, index) => {
            setTimeout(() => {
                tile.classList.add('celebration-pulse');
            }, index * 50);
        });

        // Change background color temporarily
        document.body.style.background = 'linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57)';
        document.body.style.backgroundSize = '400% 400%';
        document.body.style.animation = 'celebrationGradient 2s ease infinite';

        // Reset after celebration
        setTimeout(() => {
            this.board.classList.remove('celebration');
            this.tiles.forEach(tile => {
                tile.classList.remove('celebration-pulse');
            });
            document.body.style.background = '';
            document.body.style.animation = '';
        }, 3000);
    }

    createConfetti() {
        const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57', '#fd79a8', '#fdcb6e'];
        const confettiContainer = document.createElement('div');
        confettiContainer.className = 'confetti-container';
        confettiContainer.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        `;

        document.body.appendChild(confettiContainer);

        // Create multiple confetti pieces
        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.style.cssText = `
                    position: absolute;
                    width: 10px;
                    height: 10px;
                    background: ${colors[Math.floor(Math.random() * colors.length)]};
                    top: -10px;
                    left: ${Math.random() * 100}%;
                    animation: confettiFall ${2 + Math.random() * 3}s linear forwards;
                    transform: rotate(${Math.random() * 360}deg);
                `;

                confettiContainer.appendChild(confetti);

                // Remove confetti after animation
                setTimeout(() => {
                    if (confetti.parentNode) {
                        confetti.parentNode.removeChild(confetti);
                    }
                }, 5000);
            }, i * 100);
        }

        // Remove container after all confetti is done
        setTimeout(() => {
            if (confettiContainer.parentNode) {
                confettiContainer.parentNode.removeChild(confettiContainer);
            }
        }, 8000);
    }

    selectRandomBackground() {
        // Only select random background if user hasn't set a preference
        const backgroundSelect = document.getElementById('background-select');
        if (backgroundSelect && backgroundSelect.options.length > 1) {
            // Check if current background is still default
            if (this.currentBackground === 'default.jpg' || !this.currentBackground) {
                const randomIndex = Math.floor(Math.random() * backgroundSelect.options.length);
                const randomBackground = backgroundSelect.options[randomIndex].value;

                this.currentBackground = randomBackground;
                backgroundSelect.value = randomBackground;

                // Save the random selection as user preference
                this.saveUserPreference(randomBackground);
            }
        }
    }

    addLeaderboardRefreshButton() {
        const leaderboardPanel = document.querySelector('.leaderboard-panel');
        if (leaderboardPanel && !leaderboardPanel.querySelector('.refresh-btn')) {
            const refreshBtn = document.createElement('button');
            refreshBtn.className = 'btn btn-outline refresh-btn';
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Leaderboard';
            refreshBtn.style.marginTop = '10px';
            refreshBtn.style.width = '100%';
            refreshBtn.onclick = () => {
                refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
                this.loadLeaderboard().then(() => {
                    refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Leaderboard';
                });
            };
            leaderboardPanel.appendChild(refreshBtn);
        }
    }
}

// Global functions for modal management
function closeHistoryModal() {
    document.getElementById('history-modal').style.display = 'none';
}

// Initialize game when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.game = new SlidingPuzzle();
});
