@extends('layouts.app')
@section('content')

<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    .admin-wrap {
        min-height: 100vh;
        background: #F7F3EE;
        padding: 40px 48px;
        font-family: 'Segoe UI', system-ui, sans-serif;
    }

    /* ── En-tête ── */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 36px;
        padding-bottom: 24px;
        border-bottom: 1px solid #E0D5C8;
    }
    .page-header h1 {
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 28px;
        font-weight: normal;
        color: #2C1810;
        letter-spacing: -0.3px;
    }
    .page-header p {
        font-size: 13px;
        color: #9B8B7A;
        margin-top: 5px;
    }
    .btn-primary {
        background: #7B3A1E;
        color: white;
        border: none;
        padding: 11px 22px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        letter-spacing: 0.2px;
        transition: background 0.2s;
    }
    .btn-primary:hover { background: #5E2B14; }

    /* ── Alerte succès ── */
    .alert-success {
        background: #EAF7F0;
        color: #1A6B42;
        border: 1px solid #B6E2CC;
        padding: 13px 18px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 28px;
    }

    /* ── Tableau ── */
    .table-card {
        background: white;
        border-radius: 14px;
        box-shadow: 0 2px 16px rgba(44,24,16,0.07);
        overflow: hidden;
    }
    .table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 24px;
        border-bottom: 1px solid #F0E9E0;
    }
    .table-toolbar h2 {
        font-size: 14px;
        font-weight: 600;
        color: #2C1810;
    }
    .search-input {
        padding: 8px 14px;
        border: 1px solid #E0D5C8;
        border-radius: 7px;
        font-size: 13px;
        width: 240px;
        color: #2C1810;
        background: #FDFAF7;
        transition: border-color 0.2s;
    }
    .search-input:focus {
        outline: none;
        border-color: #7B3A1E;
    }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #FDFAF7; }
    th {
        padding: 13px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        color: #9B8B7A;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }
    td {
        padding: 14px 20px;
        border-bottom: 1px solid #F5EFE8;
        font-size: 13px;
        color: #3D2B1F;
        vertical-align: middle;
    }
    .prod-row:last-child td { border-bottom: none; }
    .prod-row:hover td { background: #FDFAF7; }

    /* Photo miniature */
    .thumb {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background: #F0E9DF;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .thumb img { width: 100%; height: 100%; object-fit: cover; }
    .thumb-placeholder {
        width: 24px;
        height: 24px;
        opacity: 0.3;
    }

    /* Nom produit */
    .prod-name { font-weight: 600; color: #2C1810; font-size: 14px; }

    /* Badge catégorie */
    .badge-cat {
        display: inline-block;
        background: #FDF0E0;
        color: #7B3A1E;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
    }

    /* Prix */
    .prix { font-weight: 600; color: #5E2B14; }

    /* Badge stock */
    .badge-stock {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .stock-ok { background: #E8F5EE; color: #1A6B42; }
    .stock-low { background: #FDE8E8; color: #B91C1C; }

    /* Boutons actions */
    .btn-edit {
        background: #FDF0E0;
        color: #7B3A1E;
        border: none;
        padding: 6px 14px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }
    .btn-edit:hover { background: #7B3A1E; color: white; }
    .btn-delete {
        background: #FDE8E8;
        color: #B91C1C;
        border: none;
        padding: 6px 14px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }
    .btn-delete:hover { background: #B91C1C; color: white; }
    .actions { display: flex; gap: 8px; }

    /* ── Modales ── */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(20,10,5,0.55);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-box {
        background: white;
        border-radius: 16px;
        padding: 36px;
        width: 680px;
        max-width: 94vw;
        max-height: 92vh;
        overflow-y: auto;
        box-shadow: 0 24px 64px rgba(20,10,5,0.25);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
    }
    .modal-header h3 {
        font-family: Georgia, serif;
        font-size: 20px;
        font-weight: normal;
        color: #2C1810;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 22px;
        cursor: pointer;
        color: #9B8B7A;
        line-height: 1;
        padding: 4px;
    }
    .modal-close:hover { color: #2C1810; }

    /* Champs formulaire */
    .form-group { margin-bottom: 18px; }
    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #5A3E30;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #E0D5C8;
        border-radius: 8px;
        font-size: 13px;
        color: #2C1810;
        background: #FDFAF7;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #7B3A1E;
        background: white;
    }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .grid-5 { display: grid; grid-template-columns: repeat(5,1fr); gap: 12px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 2fr; gap: 16px; }
    .col-span-2 { grid-column: span 2; }

    /* Aperçu image */
    .img-preview {
        margin-top: 8px;
        border-radius: 8px;
        overflow: hidden;
        width: 64px;
        height: 64px;
    }
    .img-preview img { width: 100%; height: 100%; object-fit: cover; }

    /* Boutons modal */
    /* Dynamic Pricing Styles */
    .pricing-row {
        display: grid;
        grid-template-columns: 1fr 1fr 40px;
        gap: 10px;
        margin-bottom: 10px;
        align-items: center;
    }
    .btn-remove-price {
        background: #FDE8E8;
        color: #B91C1C;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn-add-price {
        background: #FDF0E0;
        color: #7B3A1E;
        border: 1px dashed #7B3A1E;
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.2s;
    }
    .btn-add-price:hover { background: #FDF0E0; color: #5E2B14; border-style: solid; }

    .modal-footer { display: flex; gap: 12px; margin-top: 28px; }
    .btn-cancel {
        flex: 1;
        background: #F2ECE5;
        color: #7A6A5A;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-size: 13px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .ai-section {
        background: #FDFBFA;
        border: 1px dashed #D4B9A8;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 18px;
    }
    .btn-ai {
        background: linear-gradient(135deg, #7B3A1E 0%, #A65D3E 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .btn-ai:hover { transform: scale(1.02); }
    .btn-ai:disabled { opacity: 0.6; cursor: not-allowed; }

    /* Séparateur section prix */
    .section-label {
        font-size: 11px;
        font-weight: 600;
        color: #9B8B7A;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #F0E9E0;
    }
</style>

<div class="admin-wrap">

    <div class="page-header">
        <div>
            <h1>Gestion des produits</h1>
            <p>{{ count($produits) }} produit(s) enregistré(s)</p>
        </div>
        <button class="btn-primary" onclick="document.getElementById('modal-ajouter').style.display='flex'">
            Ajouter un produit
        </button>
    </div>

    @if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-card">
        <div class="table-toolbar">
            <h2>Liste des produits</h2>
            <input type="text" id="search" class="search-input" placeholder="Rechercher un produit..." onkeyup="filterProduits()">
        </div>
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Produit</th>
                    <th>Catégorie</th>
                    <th>Prix / mètre</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produits as $produit)
                <tr class="prod-row">
                    <td>
                        <div class="thumb">
                            @if($produit->image)
                                <img src="{{ asset($produit->image) }}" alt="{{ $produit->nom }}">
                            @else
                                <svg class="thumb-placeholder" viewBox="0 0 24 24" fill="none" stroke="#7B3A1E" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>
                                </svg>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="prod-name">{{ $produit->nom }}</span>
                    </td>
                    <td>
                        <span class="badge-cat">{{ $produit->categorie->nom }}</span>
                    </td>
                    <td>
                        <span class="prix">
                            @if($produit->prix_metre)
                                {{ number_format($produit->prix_metre, 0, ',', ' ') }} F
                            @else —
                            @endif
                        </span>
                    </td>
                    <td>
                        <span class="badge-stock {{ $produit->stock > 10 ? 'stock-ok' : 'stock-low' }}">
                            {{ $produit->stock }} en stock
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="btn-edit" onclick="openEditProduit(
                                {{ $produit->id }},
                                '{{ addslashes($produit->nom) }}',
                                {{ $produit->categorie_id }},
                                {{ $produit->stock }},
                                '{{ $produit->prix_metre }}',
                                '{{ $produit->prix_rouleau }}',
                                '{{ $produit->prix_pantalon }}',
                                '{{ $produit->prix_chemise }}',
                                '{{ $produit->prix_tunique }}',
                                '{{ addslashes($produit->description) }}',
                                '{{ $produit->image }}'
                            )">Modifier</button>
                            <button class="btn-edit" style="background: #E0D5C8; color: #2C1810;" onclick="openPhotosModal({{ $produit->id }})">Photos</button>
                            <form method="POST" action="{{ route('admin.produits.destroy', $produit->id) }}"
                                onsubmit="return confirm('Supprimer ce produit définitivement ?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-delete">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL AJOUTER -->
<div id="modal-ajouter" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Nouveau produit</h3>
            <button class="modal-close" onclick="document.getElementById('modal-ajouter').style.display='none'">&#x2715;</button>
        </div>
        <form method="POST" action="{{ route('admin.produits.ajouter') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid-2" style="margin-bottom:18px;">
                <div class="form-group">
                    <label class="form-label">Nom du produit *</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Catégorie *</label>
                    <select name="categorie_id" class="form-control" required>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="section-label">Tarification Dynamique</div>
            <div id="pricing-container-add" style="margin-bottom:18px;">
                <div class="pricing-row">
                    <input type="text" name="tarifications[0][label]" class="form-control" placeholder="ex: Au mètre" required>
                    <input type="number" name="tarifications[0][prix]" class="form-control" placeholder="Prix" required>
                    <button type="button" class="btn-remove-price" onclick="this.parentElement.remove()">×</button>
                </div>
            </div>
            <button type="button" class="btn-add-price" onclick="addPricingRow('pricing-container-add')">+ Ajouter une option de prix</button>

            <div class="grid-3" style="margin-bottom:8px; margin-top:20px;">
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Photos du produit</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modal-ajouter').style.display='none'">Annuler</button>
                <button type="submit" class="btn-submit">Enregistrer le produit</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL MODIFIER -->
<div id="modal-modifier" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Modifier le produit</h3>
            <button class="modal-close" onclick="document.getElementById('modal-modifier').style.display='none'">&#x2715;</button>
        </div>
        <form method="POST" id="form-modifier-produit" enctype="multipart/form-data">
            @csrf
            <div class="grid-2" style="margin-bottom:18px;">
                <div class="form-group">
                    <label class="form-label">Nom du produit *</label>
                    <input type="text" name="nom" id="edit-nom" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Catégorie *</label>
                    <select name="categorie_id" id="edit-categorie" class="form-control" required>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="section-label">Tarification Dynamique</div>
            <div id="pricing-container-edit" style="margin-bottom:18px;">
                <!-- Injected by JS -->
            </div>
            <button type="button" class="btn-add-price" onclick="addPricingRow('pricing-container-edit')">+ Ajouter une option de prix</button>

            <div class="ai-section">
                <div class="section-label" style="border:none; margin-bottom:8px;">✨ Assistant Magique (IA)</div>
                <div class="grid-2" style="gap:12px;">
                    <input type="text" id="ai-keywords" class="form-control" placeholder="Mots-clés (ex: luxe, soie, mariage)">
                    <button type="button" class="btn-ai" onclick="generateAiDescription()">Générer la description</button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description du produit</label>
                <textarea name="description" id="edit-description" class="form-control" rows="4"></textarea>
            </div>

            <div class="section-label">Référencement (SEO)</div>
            <div class="form-group">
                <label class="form-label">Titre Meta (pour Google)</label>
                <input type="text" name="meta_title" id="edit-meta-title" class="form-control" placeholder="Titre accrocheur pour les moteurs de recherche">
            </div>
            <div class="form-group">
                <label class="form-label">Description Meta</label>
                <textarea name="meta_description" id="edit-meta-description" class="form-control" rows="2" placeholder="Résumé court pour attirer le clic sur Google"></textarea>
            </div>

            <div class="grid-3" style="margin-bottom:8px; margin-top:20px;">
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" id="edit-stock" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Remplacer les photos</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <div id="edit-img-preview"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modal-modifier').style.display='none'">Annuler</button>
                <button type="submit" class="btn-submit">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PHOTOS -->
<div id="modal-photos" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="photos-title">Gestion des photos</h3>
            <button class="modal-close" onclick="document.getElementById('modal-photos').style.display='none'">&#x2715;</button>
        </div>

        <div class="mb-6">
            <div class="section-label">Photos existantes</div>
            <div id="photos-container" class="grid grid-cols-3 gap-4">
                <!-- Photos injectées ici -->
            </div>
        </div>

        <div class="border-t border-gray-100 pt-6">
            <div class="section-label">Ajouter des photos</div>
            <form method="POST" id="photos-form" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('modal-photos').style.display='none'">Fermer</button>
                    <button type="submit" class="btn-submit">Uploader les photos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterProduits() {
    const search = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('.prod-row').forEach(row => {
        const nom = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        row.style.display = nom.includes(search) ? '' : 'none';
    });
}

function addPricingRow(containerId, label = '', prix = '') {
    const container = document.getElementById(containerId);
    const index = container.children.length;
    const row = document.createElement('div');
    row.className = 'pricing-row';
    row.innerHTML = `
        <input type="text" name="tarifications[${index}][label]" class="form-control" placeholder="ex: Au mètre" value="${label}" ${containerId === 'pricing-container-add' ? 'required' : ''}>
        <input type="number" name="tarifications[${index}][prix]" class="form-control" placeholder="Prix" value="${prix}" ${containerId === 'pricing-container-add' ? 'required' : ''}>
        <button type="button" class="btn-remove-price" onclick="this.parentElement.remove()">×</button>
    `;
    container.appendChild(row);
}

const productPhotos = @json($produits->mapWithKeys(fn($p) => [$p->id => $p->images->map(fn($i) => ['id' => $i->id, 'chemin' => $i->chemin])]));

function openPhotosModal(id) {
    const modal = document.getElementById('modal-photos');
    const container = document.getElementById('photos-container');
    const title = document.getElementById('photos-title');

    const photos = productPhotos[id] || [];
    title.innerText = 'Gestion des photos du produit';

    container.innerHTML = '';
    if (photos.length === 0) {
        container.innerHTML = '<p class="text-gray-400 text-center py-4 text-sm">Aucune photo supplémentaire.</p>';
    } else {
        photos.forEach(photo => {
            container.innerHTML += `
                <div class="relative group border border-gray-200 rounded-lg p-2 bg-gray-50">
                    <img src="/${photo.chemin}" class="w-full h-32 object-cover rounded-md">
                    <form method="POST" action="/admin/images/${photo.id}" class="absolute top-2 right-2" onsubmit="return confirm('Supprimer cette photo ?')">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700">×</button>
                    </form>
                </div>
            `;
        });
    }

    document.getElementById('photos-form').action = '/admin/produits/' + id + '/images';
    modal.style.display = 'flex';
}

function openEditProduit(id, nom, categorieId, stock, prixMetre, prixRouleau, prixPantalon, prixChemise, prixTunique, description, image, metaTitle = '', metaDescription = '') {
    document.getElementById('edit-nom').value = nom;
    document.getElementById('edit-categorie').value = categorieId;
    document.getElementById('edit-stock').value = stock;
    document.getElementById('edit-prix_metre').value = prixMetre;
    document.getElementById('edit-prix_rouleau').value = prixRouleau;
    document.getElementById('edit-prix_pantalon').value = prixPantalon;
    document.getElementById('edit-prix_chemise').value = prixChemise;
    document.getElementById('edit-prix_tunique').value = prixTunique;
    document.getElementById('edit-description').value = description;
    document.getElementById('edit-meta-title').value = metaTitle;
    document.getElementById('edit-meta-description').value = metaDescription;

    const preview = document.getElementById('edit-img-preview');
    if (image) {
        preview.innerHTML = '<div class="img-preview"><img src="/' + image + '" alt="Aperçu"></div>';
    } else {
        preview.innerHTML = '';
    }
    document.getElementById('form-modifier-produit').action = '/admin/produits/' + id + '/modifier';
    document.getElementById('modal-modifier').style.display = 'flex';
}

async function generateAiDescription() {
    const productId = document.getElementById('form-modifier-produit').action.split('/').pop();
    const keywords = document.getElementById('ai-keywords').value;
    const btn = document.querySelector('.btn-ai');

    if (!keywords) {
        alert('Veuillez saisir quelques mots-clés !');
        return;
    }

    btn.disabled = true;
    btn.innerText = 'L\'IA réfléchit... ✨';

    try {
        const response = await fetch(`/admin/produits/${productId}/ai-description`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ keywords })
        });

        const data = await response.json();
        if (data.description) {
            document.getElementById('edit-description').value = data.description;
        }
    } catch (e) {
        alert('Erreur lors de la génération.');
    } finally {
        btn.disabled = false;
        btn.innerText = 'Générer la description';
    }
}

// Fermer modale en cliquant l'overlay
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>

@endsection