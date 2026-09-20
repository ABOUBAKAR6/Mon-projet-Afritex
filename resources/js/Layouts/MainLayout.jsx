import React from 'react';
import { Link } from '@inertiajs/react';
import AnimatedNav from '../Components/Header/AnimatedNav';

export default function MainLayout({ children, auth }) {
    return (
        <div className="min-h-screen bg-afri-beige selection:bg-afri-gold selection:text-white">
            {/* PROMO BAR PREMIUM */}
            <div className="bg-afri-gold text-white text-center py-2 text-xs font-medium tracking-widest uppercase">
                Livraison gratuite pour toute commande supérieure à 50 000 FCFA !
                <Link href="/catalogue" className="text-white font-bold ml-2 underline hover:text-afri-dark transition-colors">Commander maintenant</Link>
            </div>

            <AnimatedNav auth={auth} />

            {/* MAIN CONTENT */}
            <main>{children}</main>

            {/* PREMIUM FOOTER */}
            <footer className="bg-afri-dark text-white pt-20 pb-10 px-6 sm:px-10 lg:px-16 relative overflow-hidden">
                {/* Background Accent */}
                <div className="absolute top-0 right-0 w-96 h-96 bg-afri-gold/10 rounded-full blur-3xl pointer-events-none -translate-y-1/2 translate-x-1/4"></div>

                <div className="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-16 mb-20 relative z-10">
                    {/* Brand Column */}
                    <div className="col-span-1 md:col-span-1 space-y-6">
                        <div className="serif text-3xl font-bold tracking-tight">AfriTex.</div>
                        <p className="text-afri-cream/60 text-sm leading-relaxed max-w-xs">
                            L'excellence du textile africain. Nous sélectionnons les matières les plus nobles pour sublimer vos créations.
                        </p>
                        <div className="flex gap-4">
                            {['IG', 'TK', 'FB'].map(social => (
                                <a key={social} href="#" className="w-8 h-8 rounded-full border border-afri-gold/30 flex items-center justify-center text-[10px] font-bold hover:bg-afri-gold hover:text-afri-dark transition-all duration-300">
                                    {social}
                                </a>
                            ))}
                        </div>
                    </div>

                    {/* Exploration Column */}
                    <div className="space-y-6">
                        <h4 className="text-xs uppercase tracking-[0.2em] font-bold text-afri-gold">Exploration</h4>
                        <ul className="space-y-3 text-sm text-afri-cream/60">
                            <li><Link href="/catalogue" className="hover:text-white transition-colors">Tous les tissus</Link></li>
                            <li><Link href="/catalogue" className="hover:text-white transition-colors">Vente en Gros</Link></li>
                            <li><Link href="/a-propos" className="hover:text-white transition-colors">Notre Histoire</Link></li>
                            <li><Link href="/faq" className="hover:text-white transition-colors">Foire aux Questions</Link></li>
                        </ul>
                    </div>

                    {/* Assistance Column */}
                    <div className="space-y-6">
                        <h4 className="text-xs uppercase tracking-[0.2em] font-bold text-afri-gold">Assistance</h4>
                        <ul className="space-y-3 text-sm text-afri-cream/60">
                            <li><Link href="/faq" className="hover:text-white transition-colors">Centre d'aide</Link></li>
                            <li><Link href="/privacy" className="hover:text-white transition-colors">Confidentialité</Link></li>
                            <li><Link href="/cgu" className="hover:text-white transition-colors">Conditions Générales</Link></li>
                            <li><a href="https://wa.me/YOUR_NUMBER" className="hover:text-white transition-colors">WhatsApp Support</a></li>
                        </ul>
                    </div>

                    {/* Private Circle (Newsletter) */}
                    <div className="space-y-6">
                        <h4 className="text-xs uppercase tracking-[0.2em] font-bold text-afri-gold">Le Cercle Privé</h4>
                        <p className="text-sm text-afri-cream/60 leading-relaxed">
                            Rejoignez notre cercle exclusif pour accéder aux nouvelles collections en avant-première.
                        </p>
                        <form className="flex gap-2">
                            <input type="email" placeholder="Votre email" className="bg-white/10 border border-afri-gold/20 rounded-full px-4 py-2 text-xs outline-none focus:border-afri-gold transition-all flex-1 text-white placeholder:text-afri-cream/30" />
                            <button className="bg-afri-gold text-afri-dark px-4 py-2 rounded-full text-[10px] font-bold uppercase tracking-widest hover:bg-white transition-all">OK</button>
                        </form>
                    </div>
                </div>

                <div className="max-w-7xl mx-auto pt-10 border-t border-afri-gold/10 flex flex-col md:flex-row justify-between items-center gap-6 text-[10px] uppercase tracking-widest text-afri-cream/50 font-medium">
                    <span>© 2026 AfriTex — Tous droits réservés</span>
                    <span className="flex items-center gap-2">Fait avec <span className="text-afri-gold">❤️</span> en Côte d'Ivoire</span>
                </div>
            </footer>

            {/* FLOATING WHATSAPP CONCIERGE */}
            <a
                href="https://wa.me/YOUR_NUMBER"
                target="_blank"
                rel="noopener noreferrer"
                className="fixed bottom-8 right-8 z-[100] group flex items-center gap-3 bg-white p-3 rounded-full shadow-2xl border border-afri-cream hover:scale-110 transition-all duration-500"
            >
                <div className="bg-green-500 w-12 h-12 rounded-full flex items-center justify-center text-white text-2xl shadow-inner">
                    💬
                </div>
                <div className="pr-4 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-4 group-hover:translate-x-0">
                    <span className="text-[10px] font-bold uppercase tracking-tighter text-afri-dark block leading-none">Conciergerie</span>
                    <span className="text-[9px] text-afri-brown/60">Besoin d'un conseil ?</span>
                </div>
            </a>
        </div>
    );
}
