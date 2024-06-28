// Import des bibliothèques
const $ = require('jquery');
global.$ = global.jQuery = $;
require('popper.js');
require('bootstrap');

// Initialisation des fonctions Bootstrap
$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

// Import des fichiers CSS/SCSS
import './styles/app.scss';
import './styles/app.css';
