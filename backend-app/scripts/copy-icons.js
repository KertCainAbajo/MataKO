// Copies the Ionicons web component into public/ so the admin serves its icons itself
// instead of loading code from a third-party CDN. Runs before `npm run build` and `npm run dev`.
import { cpSync, rmSync } from 'node:fs';

rmSync('public/ionicons', { recursive: true, force: true });
cpSync('node_modules/ionicons/dist/ionicons', 'public/ionicons', { recursive: true });
console.log('Copied Ionicons to public/ionicons');
