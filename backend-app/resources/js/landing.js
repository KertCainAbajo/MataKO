import Lenis from 'lenis';
import 'lenis/dist/lenis.css';

// Smooth, eased page scrolling for the public landing page, including the header's section links.
// Visitors who ask their device for reduced motion keep the browser's normal scrolling.
if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    window.lenis = new Lenis({ autoRaf: true, anchors: true, lerp: 0.09 });
}
