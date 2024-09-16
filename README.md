# Road Trip Planner

## Overview

The Road Trip Planner is a web application that allows users to add, view, and manage destinations on a map. It includes features to calculate and display the total distance and time for a journey.

## Features

- Add destinations with latitude and longitude
- View destinations on an interactive map
- Remove destinations
- Calculate journey distance and time
- Clear saved data from local storage

## Tech Stack

- **Frontend:** Vue.js, Tailwind CSS, Leaflet
- **Backend:** Laravel (for API endpoints, if applicable)

## Prerequisites

Ensure you have the following installed on your local machine:

- [Node.js](https://nodejs.org/) (for running the frontend)
- [npm](https://www.npmjs.com/) or [Yarn](https://yarnpkg.com/) (for managing frontend dependencies)
- [Composer](https://getcomposer.org/) (for managing Laravel dependencies, if applicable)
- [PHP](https://www.php.net/) (for running Laravel)
- [MySQL](https://www.mysql.com/) or any other database for Laravel (if applicable)

## Setup

### Frontend Setup

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/yourusername/road-trip-planner.git
   cd road-trip-planner
2. run **npm install** to install js dependencies
3. run **composer install** to install php dependencies
4. Copy the .env.example file to .env and configure your database settings: cp .env.example .env
5. Run migrations with **php artisan migrate**
6. Install **Herd** and copy the application folder into the Herd directory. Then start up **Herd**
7. Start your Apache server and get MySQL running using XAMPP control panel
8. The application will be running on rtp.test on your browser.
