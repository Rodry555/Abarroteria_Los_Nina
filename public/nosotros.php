<?php
define('LOS_NINA_APP', true);
session_start();
require_once __DIR__ . '/../config/database.php';

$titulo = 'Nosotros — Abarrotería Los Nina';
$descripcion = 'Conoce la historia y los valores de Abarrotería Los Nina, tu tienda de barrio de confianza.';
$paginaActiva = 'nosotros';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/navbar.php';
?>

<main>
    <section class="section">
        <div class="container narrow-wrap">
            <div class="page-header">
                <h1>¿Quiénes somos?</h1>
                <p>Una tienda de barrio que crece contigo.</p>
            </div>

            <div class="card prose">
                <p>Abarrotería Los Nina nació como una tienda de barrio con un solo objetivo: <strong>llevar lo mejor de la abarrotería al alcance de tu puerta</strong>.</p>
                <p>Somos una familia apasionada por los productos frescos y de calidad. Trabajamos cada día para tener los mejores abarrotes, lácteos, bebidas y accesorios al alcance de todos.</p>
                <p>En Los Nina creemos en la confianza, el trato cercano y la calidad de lo que vendemos. ¡Bienvenidos a la familia!</p>
            </div>

            <div class="values-grid">
                <div class="value-item">
                    <h3>🤝 Trato cercano</h3>
                    <p>Te conocemos por tu nombre y te atendemos como en casa.</p>
                </div>
                <div class="value-item">
                    <h3>🌿 Productos frescos</h3>
                    <p>Seleccionamos lo mejor cada día para tu mesa.</p>
                </div>
                <div class="value-item">
                    <h3>🚚 Cerca de ti</h3>
                    <p>Pedidos simples y entrega en el barrio, sin complicaciones.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
