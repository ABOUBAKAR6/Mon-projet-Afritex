import React, { useEffect } from 'react';
import { Link } from '@inertiajs/react';
import MainLayout from '../Layouts/MainLayout';
import EntranceHero from '../Components/Hero/EntranceHero';

export default function Accueil({ categories, produits, auth }) {

    useEffect(() => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-scroll').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(40px)';
            el.style.transition = 'all 0.9s cubic-bezier(0.22, 1, 0.36, 1)';
            observer.observe(el);
        });

        return () => observer.disconnect();
    }, []);

    return (
        <MainLayout auth={auth}>
            {/* HERO SECTION */}
            <EntranceHero auth={auth} />

            {/* TRUST BAR - Refined to "Editorial" style */}
            <div className="bg-afri-dark py-4 px-10 flex flex-wrap justify-center gap-8 md:gap-16 border-y border-afri-gold/20">
                {['Livraison express', 'Vente en Gros & Détail', 'Qualité Certifiée', 'Paiement Sécurisé'].map((item, i) => (
                    <span key={i} className="text-afri-cream/60 text-[10px] uppercase tracking-[0.2em] font-medium flex items-center">
                        <span className="text-afri-gold mr-2">✦</span> {item}
                    </span>
                ))}
            </div>

            {/* CATEGORIES - Asymmetrical Editorial Layout */}
            <div className="relative py-24 px-10 bg-afri-beige overflow-hidden">
                {/* Organic Background Element */}
                <div className="absolute -top-20 -right-20 w-96 h-96 bg-afri-gold/10 rounded-full blur-3xl pointer-events-none"></div>

                <div className="max-w-7xl mx-auto">
                    <div className="flex flex-col md:flex-row justify-between items-end mb-16 gap-6">
                        <div className="animate-scroll">
                            <span className="text-afri-gold text-xs uppercase tracking-widest font-bold mb-3 block">L'Essence du Textile</span>
                            <h2 className="font-serif text-5xl md:text-7xl text-afri-dark leading-tight">
                                Nos <span className="italic text-afri-brown">Catégories</span>
                            </h2>
                        </div>
                        <Link href="/catalogue" className="animate-scroll text-afri-brown no-underline text-sm font-medium border-b border-afri-brown pb-1 hover:text-afri-dark transition-colors">
                            Explorer la collection complète →
                        </Link>
                    </div>

                    <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                        {categories.map((cat, i) => {
                            // Create a varied grid: some cards take more space
                            const isFeatured = i === 0 || i === 3;
                            return (
                                <Link
                                    key={cat.id}
                                    href={`/catalogue?categorie_id=${cat.id}`}
                                    className={`
                                        ${isFeatured ? 'col-span-2 row-span-1' : 'col-span-1'}
                                        relative group bg-white border border-afri-cream rounded-3xl p-6 text-center no-underline transition-all duration-500
                                        hover:-translate-y-2 hover:shadow-2xl hover:shadow-afri-dark/5 block overflow-hidden
                                    `}
                                >
                                    <div className="absolute -right-4 -bottom-4 text-8xl opacity-5 group-hover:scale-110 transition-transform duration-700 pointer-events-none">🧵</div>
                                    <div className="relative z-10">
                                        <div className="text-4xl mb-4 group-hover:rotate-12 transition-transform duration-500">🧵</div>
                                        <div className="text-xs font-bold uppercase tracking-wider text-afri-dark group-hover:text-afri-brown transition-colors">
                                            {cat.nom}
                                        </div>
                                    </div>
                                </Link>
                            );
                        })}
                    </div>
                </div>
            </div>

            {/* PRODUITS - High-End Asymmetric Masonry-like Grid */}
            <div className="relative py-24 px-10 bg-white overflow-hidden">
                <div className="absolute top-1/2 -left-20 w-80 h-80 bg-afri-cream rounded-full blur-3xl pointer-events-none opacity-60"></div>

                <div className="max-w-7xl mx-auto">
                    <div className="flex flex-col md:flex-row justify-between items-end mb-16 gap-6">
                        <div className="animate-scroll">
                            <span className="text-afri-gold text-xs uppercase tracking-widest font-bold mb-3 block">Sélection exclusive</span>
                            <h2 className="font-serif text-5xl md:text-7xl text-afri-dark leading-tight">
                                Pièces <span className="italic text-afri-brown">Maîtresses</span>
                            </h2>
                        </div>
                        <Link href="/catalogue" className="animate-scroll text-afri-brown no-underline text-sm font-medium border-b border-afri-brown pb-1 hover:text-afri-dark transition-colors">
                            Tout voir →
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        {produits.length > 0 ? produits.map((produit, i) => {
                            // Vary the height/scale of products to create a dynamic flow
                            const isBig = i % 5 === 0;
                            return (
                                <div key={produit.id} className={`
                                    animate-scroll relative group bg-afri-beige rounded-[40px] overflow-hidden transition-all duration-500
                                    hover:-translate-y-3 hover:shadow-2xl hover:shadow-afri-dark/10
                                    ${isBig ? 'md:col-span-2 md:row-span-1' : ''}
                                `}`} style={{ transitionDelay: `${(i % 4) * 0.1}s` }}>

                                    <div className={`relative h-64 ${isBig ? 'h-80' : ''} overflow-hidden bg-afri-cream`}>
                                        {produit.image ? (
                                            <img src={`/${produit.image}`} className="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center text-6xl">🧵</div>
                                        )}
                                        <div className="absolute top-4 left-4">
                                            <span className="bg-white/80 backdrop-blur-md text-afri-dark text-[10px] font-bold uppercase px-3 py-1 rounded-full tracking-tighter">
                                                {produit.categorie?.nom}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="p-8">
                                        <div className="flex justify-between items-start mb-4">
                                            <div className="text-lg font-serif font-bold text-afri-dark group-hover:text-afri-brown transition-colors">
                                                {produit.nom}
                                            </div>
                                            <div className="text-afri-gold font-bold">
                                                {produit.prix_metre && `${produit.prix_metre.toLocaleString()} F`}
                                            </div>
                                        </div>

                                        <Link href={`/produit/${produit.id}`} className="block w-full bg-afri-dark text-white py-3 rounded-2xl text-center text-xs font-bold uppercase tracking-widest transition-all duration-300 hover:bg-afri-brown hover:shadow-lg active:scale-95">
                                            Découvrir la pièce
                                        </Link>
                                    </div>
                                </div>
                            );
                        }) : (
                            <p className="text-afri-brown/60 col-span-full text-center py-20 font-serif italic">Notre collection est en cours de renouvellement...</p>
                        )}
                    </div>
                </div>
            </div>

            {/* GROS / DETAIL - Refined to a "Story" section */}
            <div className="relative py-24 px-10 bg-afri-beige overflow-hidden">
                <div className="absolute -bottom-20 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-afri-gold/10 rounded-full blur-3xl pointer-events-none"></div>

                <div className="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-12">
                    {[
                        { titre: 'L\'Art du Volume', desc: 'Pour les créateurs et revendeurs, nous offrons des conditions privilégiées sur les commandes en gros. Qualité industrielle, prix compétitifs.', badge: 'Vente en Gros', icon: '📦', color: 'bg-afri-dark' },
                        { titre: 'La Pièce Unique', desc: 'Achetez exactement ce dont vous avez besoin, au mètre ou à la pièce. Une sélection rigoureuse pour vos projets personnels.', badge: 'Vente au Détail', icon: '🛍️', color: 'bg-afri-brown' }
                    ].map((item, i) => (
                        <div key={i} className="animate-scroll group relative bg-white border border-afri-cream rounded-[50px] p-12 overflow-hidden transition-all duration-500 hover:shadow-2xl hover:shadow-afri-dark/5">
                            <div className="absolute -right-8 -top-8 text-9xl opacity-5 group-hover:rotate-12 transition-transform duration-700">{item.icon}</div>

                            <span className={`${item.color} text-white text-[10px] px-4 py-1 rounded-full inline-block mb-6 uppercase tracking-widest font-bold`}>
                                {item.badge}
                            </span>

                            <h3 className="font-serif text-4xl mb-4 text-afri-dark group-hover:text-afri-brown transition-colors">{item.titre}</h3>
                            <p className="text-afri-brown/70 text-base leading-relaxed mb-8 max-w-sm">{item.desc}</p>

                            <Link href="/catalogue" className="inline-flex items-center gap-3 text-afri-dark font-bold text-sm group-hover:gap-5 transition-all duration-300">
                                Explorer l'option <span className="text-afri-gold">→</span>
                            </Link>
                        </div>
                    ))}
                </div>
            </div>
        </MainLayout>
    );
}
