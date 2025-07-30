# Sliding Puzzle Game

A modern web-based 15-puzzle game built with PHP, HTML, CSS, and JavaScript. Features user authentication, game statistics tracking, leaderboards, and an admin panel for managing background images.

## Features

### Core Game Features
- **4x4 Sliding Puzzle**: Classic 15-puzzle with numbered tiles
- **Multiple Background Images**: Choose from different puzzle backgrounds
- **Move Counter**: Track the number of moves made
- **Timer**: Track completion time
- **Win Detection**: Automatic detection when puzzle is solved
- **Shuffle Function**: Generate new solvable puzzles

### User System
- **User Registration & Login**: Secure authentication system
- **User Preferences**: Save preferred background images
- **Game History**: View personal game statistics and history
- **Session Management**: Secure session handling

### Leaderboard & Statistics
- **Global Leaderboard**: View top scores from all players
- **Personal History**: Track your own game progress
- **Game Statistics**: Moves, time, and completion tracking
- **Database Storage**: All stats saved to SQLite database

### Admin Features
- **Admin Panel**: Manage background images and view statistics
- **Image Upload**: Upload new background images (JPG/PNG)
- **Image Management**: Enable/disable or delete background images
- **Game Statistics**: View overall game usage statistics
- **User Management**: Admin access controls

## Technical Stack

- **Backend**: PHP 8+ with PDO
- **Database**: SQLite (no server setup required)
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Modern CSS with gradients, backdrop filters, and responsive design
- **Icons**: Font Awesome 6.5.2

## Installation & Setup

### Prerequisites
- PHP 8.0 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- SQLite support (usually included with PHP)

### Quick Start

1. **Clone or download** the project files to your web directory

2. **Run the setup script** to initialize the database:
   ```bash
   php setup.php
   ```
   Or visit `http://localhost/your-project/setup.php` in your browser

3. **Start the development server** (if using PHP built-in server):
   ```bash
   php -S localhost:8000
   ```

4. **Access the application** at `http://localhost:8000`

### Default Admin Account
- **Username**: admin
- **Password**: admin123

## File Structure

```
webprogproj2/
├── index.php              # Entry point (redirects to login)
├── login.php              # Login/registration page
├── game.php               # Main game interface
├── admin.php              # Admin panel
├── logout.php             # Logout handler
├── setup.php              # Database initialization
├── README.md              # This file
├── css/
│   ├── style.css          # Login/registration styles
│   ├── game.css           # Game interface styles
│   └── admin.css          # Admin panel styles
├── js/
│   └── game.js            # Game logic and interactions
├── includes/
│   ├── auth.php           # Authentication class
│   ├── game_functions.php # Game-related functions
│   ├── admin_functions.php# Admin-related functions
│   └── database.php       # Database configuration and setup
├── api/
│   ├── save_game.php      # Save game statistics
│   ├── get_leaderboard.php# Fetch leaderboard data
│   ├── get_user_history.php# Fetch user game history
│   └── save_preferences.php# Save user preferences
├── images/
│   ├── default.jpg        # Default puzzle background
│   ├── nature.jpg         # Nature background
│   └── abstract.jpg       # Abstract background
└── database/
    └── sliding_puzzle.db  # SQLite database (created automatically)
```

## Game Rules

1. **Objective**: Arrange numbered tiles (1-15) in order with the empty space in the bottom-right corner
2. **Movement**: Click on tiles adjacent to the empty space to move them
3. **Winning**: Complete the puzzle in the fewest moves and shortest time possible
4. **Scoring**: Games are ranked by completion time, then by number of moves

## API Endpoints

### Game API
- `POST /api/save_game.php` - Save completed game statistics
- `GET /api/get_leaderboard.php` - Fetch global leaderboard
- `GET /api/get_user_history.php` - Fetch user's game history
- `POST /api/save_preferences.php` - Save user preferences

### Authentication
- Session-based authentication with secure password hashing
- CSRF protection through session validation
- Role-based access control (admin/user)

## Browser Compatibility

- **Modern Browsers**: Chrome 80+, Firefox 75+, Safari 13+, Edge 80+
- **Features Used**: CSS Grid, Flexbox, Backdrop Filter, ES6+ JavaScript
- **Responsive Design**: Works on desktop, tablet, and mobile devices

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with default algorithm
- **SQL Injection Protection**: All queries use prepared statements
- **Session Security**: Secure session management with regeneration
- **File Upload Security**: Image validation and size limits
- **Input Validation**: Server-side validation for all user inputs

## Development Notes

### Database Schema
- **users**: User accounts and admin flags
- **game_stats**: Individual game completion records
- **user_preferences**: User settings and preferences
- **background_images**: Available puzzle backgrounds

### CSS Architecture
- **Modern Design**: Gradient backgrounds, glass morphism effects
- **Responsive Layout**: CSS Grid and Flexbox for all layouts
- **Component-Based**: Modular CSS with reusable classes
- **Accessibility**: Focus states and keyboard navigation support

### JavaScript Features
- **ES6+ Syntax**: Classes, arrow functions, async/await
- **Modular Design**: Single game class with clear separation of concerns
- **Event Handling**: Efficient event delegation and management
- **API Integration**: Fetch API for all server communications

## License

This project is created for educational purposes. Feel free to use and modify as needed.

## Support

For issues or questions, please check the code comments or create an issue in the project repository.
