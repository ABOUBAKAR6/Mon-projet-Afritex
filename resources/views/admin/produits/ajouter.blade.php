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
    .btn-back {
        background: #F2ECE5;
        color: #7A6A5A;
        border: none;
        padding: 11px 22px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-back:hover { background: #E0D5C8; }
    .form-card {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(44,24,16,0.08);
        max-width: 800px;
        margin: 0 auto;
    }
    .form-group { margin-bottom: 20px; }
    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #5A3E30;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #E0D5C8;
        border-radius: 8px;
        font-size: 14px;
        color: #2C1810;
        background: #FDFAF7;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #7B3A1E;
        background: white;
    }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-5 { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; }
    .section-label {
        font-size: 11px;
        font-weight: 600;
        color: #9B8B7A;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid #F0E9E0;
        display: block;
    }
    .btn-submit {
        background: #7B3A1E;
        color: white;
        border: none;
        padding: 14px 30px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-submit:hover { background: #5E2B14; }
</style>

<div class="admin-wrap">
    <div class="page-header">
        <div>
            <h1>Ajouter un produit</h1>
            <p>Enregistrez une nouvelle pièce dans le catalogue</p>
        </div>
        <a href="{{ route('admin.produits') }}" class="btn-back">Retour à la liste</a>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route('admin.produits.ajouter') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Nom du produit *</label>
                    <input type="text" name="nom" class="form-control" required placeholder="ex: Wax Hollandais Premium">
                </div>
                <div class="form-group">
                    <label class="form-label">Catégorie *</label>
                    <select name="categorie_id" class="form-control" required>
                        <option value="">Sélectionnez une catégorie</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Détails sur la matière, le motif..."></textarea>
            </div>

            <span class="section-label">Tarification</span>
            <div class="grid-5" style="margin-bottom: 25px;">
                @foreach([['prix_metre','Au mètre'], ['prix_rouleau','Au rouleau'], ['prix_pantalon','Pantalon'], ['prix_chemise','Chemise'], ['prix_tunique','Tunique']] as $p)
                <div class="form-group">
                    <label class="form-label">{{ $p[1] }}</label>
                    <input type="number" name="{{ $p[0] }}" class="form-control" placeholder="0">
                </div>
                @endforeach
            </div>

            <div class="grid-2" style="align-items: end;">
                <div class="form-group">
                    <label class="form-label">Stock initial</label>
                    <input type="number" name="stock" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Photo principale</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>

            <div style="text-align: right; margin-top: 30px;">
                <button type="submit" class="btn-submit">Enregistrer le produit</button>
            </div>
        </form>
    </div>
</div>

@endsection
