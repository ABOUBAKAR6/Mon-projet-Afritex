<!-- Cart Badge Component -->
<span id="cartBadge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
    0
</span>

<script>
    // Mettre à jour le badge du panier
    function updateCartBadge() {
        const panier = cookieManager.getPanier();
        const badge = document.getElementById('cartBadge');
        if (badge) {
            badge.textContent = panier.length;
            badge.style.display = panier.length > 0 ? 'flex' : 'none';
        }
    }

    // Mettre à jour au chargement
    document.addEventListener('DOMContentLoaded', updateCartBadge);

    // Écouter les changements de panier (via événement custom)
    document.addEventListener('panierUpdated', updateCartBadge);
</script>
