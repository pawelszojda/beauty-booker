import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devServerUrl = env.VITE_DEV_SERVER_URL;
    const devServer = devServerUrl ? new URL(devServerUrl) : null;

    return {
        server: {
            host: '0.0.0.0',
            origin: devServerUrl,
            cors: true,
            allowedHosts: true,
            hmr: devServer
                ? {
                      host: devServer.hostname,
                      protocol: devServer.protocol.replace(':', ''),
                      clientPort: Number(devServer.port || 5173),
                  }
                : undefined,
        },
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
    };
});
