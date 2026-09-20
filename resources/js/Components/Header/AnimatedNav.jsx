import React, { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';

export default function AnimatedNav({ auth }) {
    const [isVisible, setIsVisible] = useState(true);
    const [lastScrollY, setLastScrollY] = useState(0);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [searchOpen, setSearchOpen] = useState(false);
    const [openAide, setOpenAide] = useState(false);

    useEffect(() => {
        const handleScroll = () => {
            const currentScrollY = window.scrollY;
            if (currentScrollY < 10) {
                setIsVisible(true);
            } else if (currentScrollY > lastScrollY) {
                setIsVisible(false);
            } else {
                setIsVisible(true);
            }
            setLastScrollY(currentScrollY);
        };

        window.addEventListener('scroll', handleScroll, { passive: true });
        return () => window.removeEventListener('scroll', handleScroll);
    }, [lastScrollY]);

    return (
        <>
            <nav
                className={`fixed top-0 left-0 right-0 z-50 transition-transform duration-300 ease-in-out bg-afri-dark text-afri-cream shadow-lg ${isVisible ? 'translate-y-0' : '-translate-y-full'}`}
            >
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center h-20">

                        {/* Burger Trigger */}
                        <button onClick={() => setMobileMenuOpen(true)} className="text-afri-cream p-2 focus:outline-none mr-2 hover:text-afri-gold transition">
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        {/* Logo */}
                        <div className="flex-shrink-0 flex items-center">
                            <Link href="/" className="text-3xl font-serif font-bold whitespace-nowrap tracking-tight">
                                Afri<span className="text-afri-gold">Tex</span>.
                            </Link>
                        </div>

                        {/* Search (Desktop) */}
                        <div className="hidden md:flex flex-1 justify-center px-8">
                            <form className="flex w-full max-w-lg" onSubmit={(e) => e.preventDefault()}>
                                <input type="text" placeholder="Recherchez un tissu..."
                                    className="w-full px-6 py-2 rounded-l-full text-sm outline-none text-white bg-white/10 border border-white/10 placeholder:text-white/40 focus:bg-white focus:text-gray-800 transition-all"
                                />
                                <button className="bg-afri-gold text-white px-6 py-2 rounded-r-full text-sm font-bold hover:bg-opacity-90 transition uppercase tracking-widest">
                                    Rechercher
                                </button>
                            </form>
                        </div>

                        {/* Actions */}
                        <div className="flex items-center gap-6">
                            <div className="relative">
                                <button onClick={() => setOpenAide(!openAide)} className="text-sm font-medium hover:text-afri-gold transition flex items-center gap-1 uppercase tracking-widest">
                                    Aide
                                    <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                {openAide && (
                                    <div className="absolute right-0 mt-4 w-48 bg-white text-gray-800 rounded-2xl shadow-2xl py-4 z-[60] border border-afri-cream">
                                        <Link href="/faq" className="block px-6 py-3 text-xs hover:bg-afri-beige transition">FAQ & Aide</Link>
                                        <Link href="/privacy" className="block px-6 py-3 text-xs hover:bg-afri-beige transition">Confidentialité</Link>
                                        <Link href="/cgu" className="block px-6 py-3 text-xs hover:bg-afri-beige transition">CGU</Link>
                                    </div>
                                )}
                            </div>

                            <button onClick={() => setSearchOpen(!searchOpen)} className="text-afri-cream p-2 focus:outline-none transition hover:text-afri-gold md:hidden">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>

                            {auth?.user ? (
                                <>
                                    <Link href="/panier" className="text-sm relative hover:text-afri-gold transition uppercase tracking-widest font-medium">
                                        Panier
                                    </Link>
                                    {auth.user.role === 'admin' && (
                                        <Link href="/admin" className="text-sm text-afri-gold hover:underline uppercase tracking-widest font-bold">⚙️ Admin</Link>
                                    )}
                                    <Link href="/logout" method="post" as="button" className="bg-white/10 border border-white/20 text-white px-4 py-2 rounded-full text-xs hover:bg-white/20 transition uppercase tracking-widest">Déconnexion</Link>
                                </>
                            ) : (
                                <>
                                    <Link href="/login" className="hidden sm:flex items-center gap-2 text-sm hover:text-afri-gold transition uppercase tracking-widest font-medium">
                                        Connexion
                                    </Link>
                                    <Link href="/register" className="bg-afri-gold text-white px-6 py-2 rounded-full text-xs font-bold hover:bg-opacity-90 transition uppercase tracking-widest shadow-lg shadow-afri-gold/20">S'inscrire</Link>
                                </>
                            )}
                        </div>
                    </div>
                </div>

                {/* Mobile Search */}
                {searchOpen && (
                    <div className="md:hidden bg-afri-dark border-t border-white/10 p-4">
                        <form className="flex w-full" onSubmit={(e) => e.preventDefault()}>
                            <input type="text" placeholder="Recherchez..." className="w-full px-4 py-2 rounded-l-lg text-sm text-gray-800 outline-none" />
                            <button className="bg-afri-gold text-white px-4 py-2 rounded-r-lg text-sm font-semibold">Chercher</button>
                        </form>
                    </div>
                )}
            </nav>

            {/* Mobile Menu Drawer */}
            {mobileMenuOpen && (
                <>
                    <div onClick={() => setMobileMenuOpen(false)} className="fixed inset-0 bg-black/50 z-[90] transition-opacity duration-300"></div>
                    <div className="fixed top-0 left-0 w-80 h-full bg-white shadow-2xl z-[100] transition-transform transform translate-x-0 animate-slide-in">
                        <div className="flex flex-col h-full">
                            <div className="bg-afri-dark p-6 text-afri-cream flex justify-between items-center">
                                <span className="text-xl font-serif font-bold">Menu</span>
                                <button onClick={() => setMobileMenuOpen(false)} className="text-white text-2xl">&times;</button>
                            </div>
                            <div className="flex-1 overflow-y-auto p-6 space-y-8">
                                <div>
                                    <h3 className="text-gray-400 text-xs font-bold uppercase tracking-widest mb-4">Exploration</h3>
                                    <div className="space-y-3">
                                        <Link href="/catalogue" onClick={() => setMobileMenuOpen(false)} className="block text-gray-700 hover:text-afri-brown transition font-medium py-2 border-b border-gray-50">Tous les tissus</Link>
                                        <Link href="/a-propos" onClick={() => setMobileMenuOpen(false)} className="block text-gray-700 hover:text-afri-brown transition font-medium py-2 border-b border-gray-50">Notre Histoire</Link>
                                        <Link href="/faq" onClick={() => setMobileMenuOpen(false)} className="block text-gray-700 hover:text-afri-brown transition font-medium py-2 border-b border-gray-50">FAQ & Aide</Link>
                                    </div>
                                </div>
                                <div>
                                    <h3 className="text-gray-400 text-xs font-bold uppercase tracking-widest mb-4">Légal</h3>
                                    <div className="space-y-3">
                                        <Link href="/privacy" onClick={() => setMobileMenuOpen(false)} className="block text-gray-700 hover:text-afri-brown transition py-2 border-b border-gray-50">Confidentialité</Link>
                                        <Link href="/cgu" onClick={() => setMobileMenuOpen(false)} className="block text-gray-700 hover:text-afri-brown transition py-2 border-b border-gray-50">CGU</Link>
                                    </div>
                                </div>
                            </div>
                            <div className="p-6 bg-gray-50 border-t">
                                {auth?.user ? (
                                    <Link href="/logout" method="post" as="button" className="block w-full text-center bg-red-500 text-white py-2 rounded-lg font-bold">Déconnexion</Link>
                                ) : (
                                    <>
                                        <Link href="/login" onClick={() => setMobileMenuOpen(false)} className="block w-full text-center bg-afri-dark text-white py-2 rounded-lg font-bold mb-2">Connexion</Link>
                                        <Link href="/register" onClick={() => setMobileMenuOpen(false)} className="block w-full text-center bg-afri-gold text-white py-2 rounded-lg font-bold">S'inscrire</Link>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </>
            )}
        </>
    );
}
