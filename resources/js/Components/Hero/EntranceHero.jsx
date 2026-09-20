import React, { useEffect, useRef, useState } from 'react';
import { Link } from '@inertiajs/react';

const TIMINGS = {
    easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
};

export default function EntranceHero({ auth }) {
    const [isResolved, setIsResolved] = useState(false);

    // Reliability: Use a very stable video source.
    // Note: In production, the user should put a file in /public/videos/hero.mp4
    const LUXURY_VIDEO_URL = "https://cdn.pixabay.com/vimeo/462372252/fabric-52575.mp4?width=1280&hash=38d6b6f10e170e1b8a0d5f5a5a5a5a5a5a5a5a5a";
    const FALLBACK_IMAGE = "https://images.unsplash.com/photo-1528459801613-067f51839a96?q=80&w=3840&auto=format&fit=crop";

    useEffect(() => {
        // Fast resolve to avoid "nothing" screen
        const timer = setTimeout(() => setIsResolved(true), 500);
        return () => clearTimeout(timer);
    }, []);

    return (
        <div className="relative h-screen w-full overflow-hidden bg-afri-dark">

            {/* Cinematic Video Background */}
            <div className="absolute inset-0 w-full h-full z-0">
                <video
                    autoPlay
                    muted
                    loop
                    playsInline
                    poster={FALLBACK_IMAGE}
                    className="w-full h-full object-cover opacity-60"
                >
                    <source src={LUXURY_VIDEO_URL} type="video/mp4" />
                    <source src="https://cdn.pixabay.com/vimeo/462372252/fabric-52575.mp4" type="video/mp4" />
                    Your browser does not support the video tag.
                </video>

                {/* Premium Editorial Overlay - Slightly lighter to ensure visibility */}
                <div
                    className="absolute inset-0 z-10"
                    style={{
                        background: 'linear-gradient(to bottom, rgba(74,27,12,0.6) 0%, rgba(74,27,12,0.2) 40%, rgba(74,27,12,0.7) 100%)'
                    }}
                />
            </div>

            {/* Hero Content */}
            <div className="relative z-20 h-full flex flex-col items-center justify-center text-center px-6">
                <h1
                    className={`text-5xl md:text-8xl font-serif text-white leading-tight transition-all duration-1000 ${isResolved ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}
                    style={{ transitionTimingFunction: TIMINGS.easing }}
                >
                    L'Excellence <span className="text-afri-gold italic">Textile</span>,<br/>Livrée chez vous
                </h1>

                <p
                    className={`mt-6 text-lg md:text-2xl text-afri-cream opacity-80 max-w-3xl transition-all duration-1000 delay-300 ${isResolved ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}
                    style={{ transitionTimingFunction: TIMINGS.easing }}
                >
                    Une sélection rigoureuse des étoffes les plus nobles d'Afrique.<br/>
                    <span className="text-sm uppercase tracking-[0.3em] font-bold opacity-60 mt-4 block">Vente en gros & détail</span>
                </p>

                <div className={`mt-12 flex gap-6 justify-center transition-all duration-1000 delay-500 ${isResolved ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}
                    style={{ transitionTimingFunction: TIMINGS.easing }}>
                    <Link
                        href="/catalogue"
                        className="bg-white text-afri-dark px-10 py-4 rounded-full font-bold text-sm transition-all duration-500 hover:-translate-y-1 hover:shadow-2xl hover:bg-afri-gold hover:text-white active:scale-95"
                    >
                        Explorer la collection
                    </Link>
                    {!auth?.user && (
                        <Link
                            href="/register"
                            className="border-2 border-white/40 text-white px-10 py-4 rounded-full text-sm transition-all duration-500 hover:bg-white/10 active:scale-95"
                        >
                            Rejoindre le Cercle
                        </Link>
                    )}
                </div>
            </div>
        </div>
    );
}
