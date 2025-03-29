import fs from 'fs'
import path from 'path'
import os from 'os'
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

function getLocalIP() {
    const interfaces = os.networkInterfaces()
    for (const iface of Object.values(interfaces)) {
        for (const config of iface) {
            if (config.family === 'IPv4' && !config.internal) {
                return config.address
            }
        }
    }
    return 'localhost'
}

const localIP = getLocalIP()

export default defineConfig({
    define: {
        'process.env': {}
    },
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
            host: localIP,
            port: 5173,
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/pusher-listener.js', 'resources/js/push-register.js'],
            refresh: true,
        }),
    ],
})
