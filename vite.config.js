import fs from 'fs'
import path from 'path'
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        https: {
            key: fs.readFileSync(path.resolve(__dirname, './ssl/cert-key.pem')),
            cert: fs.readFileSync(path.resolve(__dirname, './ssl/cert.pem')),
        },
        headers: {
            'Access-Control-Allow-Origin': '*',
        },
        hmr: {
            protocol: 'wss',
            host: '192.168.0.108', // seu IP na rede
            port: 5173,
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
})
