"use strict";

// Wait for the DOM and Firebase auth to be ready before running the game logic
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('auth-ready', initializeGame);
});

function initializeGame() {
    // --- DOM Elements ---
    const puzzleArea = document.getElementById('puzzlearea');
    const shuffleButton = document.getElementById('shufflebutton');
    const moveCountSpan = document.getElementById('move-count');
    const timerSpan = document.getElementById('timer');
    const winModal = document.getElementById('win-modal');
    const closeModalButton = document.getElementById('close-modal');
    
    // --- Game State Variables ---
    const PUZZLE_SIZE = 4;
    const TILE_SIZE = 100;
    let emptyRow = 3;
    let emptyCol = 3;
    let tiles = [];
    let moveCount = 0;
    let timerInterval = null;
    let seconds = 0;
    let isGameActive = false;

    /**
     * Creates the puzzle tiles and adds them to the DOM.
     */
    function createTiles() {
        puzzleArea.innerHTML = '';
        tiles = [];
        let tileNumber = 1;
        for (let row = 0; row < PUZZLE_SIZE; row++) {
            for (let col = 0; col < PUZZLE_SIZE; col++) {
                if (row === PUZZLE_SIZE - 1 && col === PUZZLE_SIZE - 1) break;

                const tile = document.createElement('div');
                tile.className = 'puzzlepiece';
                tile.textContent = tileNumber;
                
                // Store original and current positions for win checking and logic
                tile.dataset.row = row;
                tile.dataset.col = col;
                tile.dataset.originalPosition = `${row},${col}`;

                positionTile(tile, row, col);
                puzzleArea.appendChild(tile);
                tiles.push(tile);
                tileNumber++;
            }
        }
        addTileEventListeners();
        updateMovableHighlight();
    }

    /**
     * Positions a tile on the grid and sets its background.
     * @param {HTMLElement} tile - The tile element to position.
     * @param {number} row - The row index.
     * @param {number} col - The column index.
     */
    function positionTile(tile, row, col) {
        tile.style.left = `${col * TILE_SIZE}px`;
        tile.style.top = `${row * TILE_SIZE}px`;
        
        const originalRow = parseInt(tile.dataset.originalPosition.split(',')[0]);
        const originalCol = parseInt(tile.dataset.originalPosition.split(',')[1]);
        tile.style.backgroundPosition = `-${originalCol * TILE_SIZE}px -${originalRow * TILE_SIZE}px`;
        
        tile.dataset.row = row;
        tile.dataset.col = col;
    }

    /**
     * Adds click and hover event listeners to all tiles.
     */
    function addTileEventListeners() {
        tiles.forEach(tile => {
            tile.addEventListener('click', () => onTileClick(tile));
            tile.addEventListener('mouseover', () => onTileHover(tile));
            tile.addEventListener('mouseout', () => onTileOut(tile));
        });
    }

    /**
     * Handles the click event on a tile.
     * @param {HTMLElement} tile - The clicked tile.
     */
    function onTileClick(tile) {
        if (!isGameActive) {
            startGame();
        }
        if (isMovable(tile)) {
            moveTile(tile);
            updateMovableHighlight();
            if (checkWin()) {
                endGame();
            }
        }
    }

    /**
     * Handles the mouseover event on a tile.
     * @param {HTMLElement} tile - The hovered tile.
     */
    function onTileHover(tile) {
        if (isMovable(tile)) {
            tile.classList.add('movablepiece');
        }
    }

    /**
     * Handles the mouseout event on a tile.
     * @param {HTMLElement} tile - The tile the mouse left.
     */
    function onTileOut(tile) {
        tile.classList.remove('movablepiece');
    }

    /**
     * Checks if a tile is adjacent to the empty space.
     * @param {HTMLElement} tile - The tile to check.
     * @returns {boolean} - True if the tile is movable.
     */
    function isMovable(tile) {
        const row = parseInt(tile.dataset.row);
        const col = parseInt(tile.dataset.col);
        const rowDiff = Math.abs(row - emptyRow);
        const colDiff = Math.abs(col - emptyCol);
        return (rowDiff === 1 && colDiff === 0) || (rowDiff === 0 && colDiff === 1);
    }

    /**
     * Moves a tile to the empty space.
     * @param {HTMLElement} tile - The tile to move.
     */
    function moveTile(tile) {
        const tileRow = parseInt(tile.dataset.row);
        const tileCol = parseInt(tile.dataset.col);

        // Swap positions
        positionTile(tile, emptyRow, emptyCol);
        emptyRow = tileRow;
        emptyCol = tileCol;

        // Update move count
        moveCount++;
        moveCountSpan.textContent = moveCount;
    }

    /**
     * Updates the highlight on all tiles that can be moved.
     */
    function updateMovableHighlight() {
        tiles.forEach(tile => {
            if (isMovable(tile)) {
                // The hover class is now managed by CSS :hover
            } else {
                tile.classList.remove('movablepiece');
            }
        });
    }

    /**
     * Shuffles the board by making random valid moves.
     */
    function shuffle() {
        resetGame();
        let shuffleMoves = 200; // Number of random moves to shuffle
        for (let i = 0; i < shuffleMoves; i++) {
            const movableNeighbors = tiles.filter(tile => isMovable(tile));
            const randomNeighbor = movableNeighbors[Math.floor(Math.random() * movableNeighbors.length)];
            // This move should not count towards the player's score
            const tempMoveCount = moveCount;
            moveTile(randomNeighbor);
            moveCount = tempMoveCount; // Restore move count
        }
        moveCountSpan.textContent = moveCount; // Set display to 0
        updateMovableHighlight();
    }

    /**
     * Checks if the puzzle is in its solved state.
     * @returns {boolean} - True if the puzzle is solved.
     */
    function checkWin() {
        if (emptyRow !== 3 || emptyCol !== 3) return false;
        return tiles.every(tile => {
            const originalPos = tile.dataset.originalPosition.split(',');
            return parseInt(tile.dataset.row) === parseInt(originalPos[0]) &&
                   parseInt(tile.dataset.col) === parseInt(originalPos[1]);
        });
    }

    /**
     * Starts the game timer and sets the game state to active.
     */
    function startGame() {
        if (isGameActive) return;
        isGameActive = true;
        timerInterval = setInterval(() => {
            seconds++;
            timerSpan.textContent = `${seconds}s`;
        }, 1000);
    }

    /**
     * Ends the game, stops the timer, shows the win modal, and saves the score.
     */
    function endGame() {
        isGameActive = false;
        clearInterval(timerInterval);
        
        // Show Win Modal
        document.getElementById('final-time').textContent = `${seconds}s`;
        document.getElementById('final-moves').textContent = moveCount;
        winModal.style.display = 'flex';

        // Save score to Firestore
        if (window.saveScore) {
            window.saveScore(seconds, moveCount);
        }
    }

    /**
     * Resets the game state to its initial configuration.
     */
    function resetGame() {
        isGameActive = false;
        clearInterval(timerInterval);
        seconds = 0;
        moveCount = 0;
        timerSpan.textContent = '0s';
        moveCountSpan.textContent = '0';
        emptyRow = 3;
        emptyCol = 3;
        createTiles();
        winModal.style.display = 'none';
    }

    // --- Event Listeners ---
    shuffleButton.addEventListener('click', shuffle);
    closeModalButton.addEventListener('click', resetGame);

    // --- Initial Setup ---
    createTiles();
}
