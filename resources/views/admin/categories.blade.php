@extends('layouts.app')
@section('content')

<div style="padding:40px; background:#f9f5f0; min-height:100vh;">

    <!-- HEADER -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <div>
            <h2 style="font-family:Georgia,serif; font-size:32px; color:#4A1B0C; margin:0;">📂 Catégories de tissus</h2>
            <p style="color:#7a6a5a; font-size:14px; margin-top:4px;">{{ count($categories) }} catégorie(s) au total</p>
        </div>
        <button onclick="document.getElementById('modal-ajouter').style.display='flex'"
            style="background:linear-gradient(135deg, #8B4513, #C8963E); color:white; border:none; padding:12px 24px; border-radius:12px; cursor:pointer; font-size:14px; font-weight:600; display:flex; align-items:center; gap:8px; box-shadow:0 4px 15px rgba(139,69,19,0.3); transition:all 0.2s ease;"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(139,69,19,0.4)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(139,69,19,0.3)'">
            Ajouter une catégorie
        </button>
    </div>

    <!-- STATS -->
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:32px;">
        <div style="background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(139,69,19,0.06); display:flex; align-items:center; gap:16px;">
            <div style="width:48px; height:48px; background:linear-gradient(135deg, #8B4513, #C8963E); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px;">📂</div>
            <div>
                <div style="font-size:28px; font-weight:700; color:#4A1B0C;">{{ count($categories) }}</div>
                <div style="font-size:13px; color:#7a6a5a;">Total catégories</div>
            </div>
        </div>
        <div style="background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(139,69,19,0.06); display:flex; align-items:center; gap:16px;">
            <div style="width:48px; height:48px; background:linear-gradient(135deg, #059669, #34d399); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px;">Produits</div>
            <div>
                <div style="font-size:28px; font-weight:700; color:#4A1B0C;">{{ $categories->sum(fn($c) => $c->produits->count()) }}</div>
                <div style="font-size:13px; color:#7a6a5a;">Total produits</div>
            </div>
        </div>
        <div style="background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(139,69,19,0.06); display:flex; align-items:center; gap:16px;">
            <div style="width:48px; height:48px; background:linear-gradient(135deg, #3b82f6, #60a5fa); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px;">📊</div>
            <div>
                <div style="font-size:28px; font-weight:700; color:#4A1B0C;">{{ round($categories->avg(fn($c) => $c->produits->count())) }}</div>
                <div style="font-size:13px; color:#7a6a5a;">Moy. produits/catégorie</div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div style="background:white; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(139,69,19,0.08);">
        <div style="padding:20px 24px; border-bottom:1px solid #e8ddd3; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="color:#4A1B0C; margin:0; font-size:16px;">Liste des catégories</h3>
            <input type="text" id="search" placeholder="Rechercher..." onkeyup="filterCategories()" 
                style="padding:8px 14px; border:1px solid #e8ddd3; border-radius:8px; font-size:13px; width:200px;">
        </div>
        <table style="width:100%; border-collapse:collapse;" id="categoriesTable">
            <thead>
                <tr style="background:#FDF6EE;">
                    <th style="padding:14px 24px; text-align:left; color:#7a6a5a; font-size:12px; text-transform:uppercase; letter-spacing:0.5px;">#</th>
                    <th style="padding:14px 24px; text-align:left; color:#7a6a5a; font-size:12px; text-transform:uppercase; letter-spacing:0.5px;">Image</th>
                    <th style="padding:14px 24px; text-align:left; color:#7a6a5a; font-size:12px; text-transform:uppercase; letter-spacing:0.5px;">Nom</th>
                    <th style="padding:14px 24px; text-align:left; color:#7a6a5a; font-size:12px; text-transform:uppercase; letter-spacing:0.5px;">Nb. produits</th>
                    <th style="padding:14px 24px; text-align:left; color:#7a6a5a; font-size:12px; text-transform:uppercase; letter-spacing:0.5px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $categorie)
                <tr class="cat-row" style="transition:background 0.2s ease;"
                    onmouseover="this.style.background='#FDF6EE'"
                    onmouseout="this.style.background='white'">
                    <td style="padding:16px 24px; color:#7a6a5a; font-size:14px; border-bottom:1px solid #f5f0eb;">{{ $categorie->id }}</td>
                    <td style="padding:16px 24px; border-bottom:1px solid #f5f0eb;">
                        <div style="width:44px; height:44px; background:linear-gradient(135deg, #F5E6D3, #EDD5B8); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; overflow:hidden;">
                            @if($categorie->image)
                                <img src="{{ asset($categorie->image) }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <span class="text-sm text-gray-400">Image non disponible</span>
                            @endif
                        </div>
                    </td>
                    <td style="padding:16px 24px; border-bottom:1px solid #f5f0eb;">
                        <div style="font-weight:600; color:#4A1B0C; font-size:15px;">{{ $categorie->nom }}</div>
                    </td>
                    <td style="padding:16px 24px; border-bottom:1px solid #f5f0eb;">
                        <span style="background:#FEF3C7; color:#92400e; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;">
                            {{ $categorie->produits->count() }} produit(s)
                        </span>
                    </td>
                    <td style="padding:16px 24px; border-bottom:1px solid #f5f0eb;">
                        <div style="display:flex; gap:8px;">
                            <button onclick="openEditModal({{ $categorie->id }}, '{{ $categorie->nom }}')"
                                style="background:#EFF6FF; color:#1d4ed8; border:none; padding:8px 14px; border-radius:8px; cursor:pointer; font-size:13px; font-weight:500; transition:all 0.2s ease;"
                                onmouseover="this.style.background='#1d4ed8'; this.style.color='white'"
                                onmouseout="this.style.background='#EFF6FF'; this.style.color='#1d4ed8'">
                                Modifier
                            </button>
                            <form method="POST" action="{{ route('admin.categories.destroy', $categorie->id) }}" 
                                onsubmit="return confirm('Supprimer cette catégorie ?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    style="background:#FEE2E2; color:#dc2626; border:none; padding:8px 14px; border-radius:8px; cursor:pointer; font-size:13px; font-weight:500; transition:all 0.2s ease;"
                                    onmouseover="this.style.background='#dc2626'; this.style.color='white'"
                                    onmouseout="this.style.background='#FEE2E2'; this.style.color='#dc2626'">
                                    Supprimer
                                </button>
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
<div id="modal-ajouter" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:20px; padding:32px; width:500px; max-width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="font-family:Georgia,serif; font-size:22px; color:#4A1B0C; margin:0;">Ajouter une catégorie</h3>
            <button onclick="document.getElementById('modal-ajouter').style.display='none'" style="background:none; border:none; font-size:16px; cursor:pointer; color:#7a6a5a;">Fermer</button>
        </div>
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:14px; color:#4A1B0C; margin-bottom:8px; font-weight:500;">Nom de la catégorie</label>
                <input type="text" name="nom" required placeholder="Ex: Grain de Diamant"
                    style="width:100%; padding:12px; border:1px solid #e8ddd3; border-radius:8px; font-size:14px;">
            </div>
            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:14px; color:#4A1B0C; margin-bottom:8px; font-weight:500;">Image (optionnel)</label>
                <input type="file" name="image" accept="image/*"
                    style="width:100%; padding:12px; border:1px solid #e8ddd3; border-radius:8px; font-size:14px;">
            </div>
            <div style="display:flex; gap:12px;">
                <button type="button" onclick="document.getElementById('modal-ajouter').style.display='none'"
                    style="flex:1; background:#f5f0eb; color:#7a6a5a; border:none; padding:12px; border-radius:8px; cursor:pointer; font-size:14px;">
                    Annuler
                </button>
                <button type="submit"
                    style="flex:2; background:#8B4513; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600;">
                    Ajouter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL MODIFIER -->
<div id="modal-modifier" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:20px; padding:32px; width:500px; max-width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <h3 style="font-family:Georgia,serif; font-size:22px; color:#4A1B0C; margin:0;">Modifier la catégorie</h3>
            <button onclick="document.getElementById('modal-modifier').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color:#7a6a5a;">Fermer</button>
        </div>
        <form method="POST" id="form-modifier" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:14px; color:#4A1B0C; margin-bottom:8px; font-weight:500;">Nom de la catégorie</label>
                <input type="text" name="nom" id="edit-nom" required
                    style="width:100%; padding:12px; border:1px solid #e8ddd3; border-radius:8px; font-size:14px;">
            </div>
            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:14px; color:#4A1B0C; margin-bottom:8px; font-weight:500;">Image (optionnel)</label>
                <input type="file" name="image" accept="image/*"
                    style="width:100%; padding:12px; border:1px solid #e8ddd3; border-radius:8px; font-size:14px;">
            </div>
            <div style="display:flex; gap:12px;">
                <button type="button" onclick="document.getElementById('modal-modifier').style.display='none'"
                    style="flex:1; background:#f5f0eb; color:#7a6a5a; border:none; padding:12px; border-radius:8px; cursor:pointer; font-size:14px;">
                    Annuler
                </button>
                <button type="submit"
                    style="flex:2; background:#8B4513; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600;">
                    Modifier
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, nom) {
    document.getElementById('edit-nom').value = nom;
    document.getElementById('form-modifier').action = '/admin/categories/' + id;
    document.getElementById('modal-modifier').style.display = 'flex';
}

function filterCategories() {
    const search = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('.cat-row').forEach(row => {
        const nom = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        row.style.display = nom.includes(search) ? '' : 'none';
    });
}
</script>

@endsection