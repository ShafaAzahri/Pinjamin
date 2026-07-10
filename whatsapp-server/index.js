const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const cors = require('cors');

const app = express();
const port = 3000;

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

let isClientReady = false;

// Inisialisasi WhatsApp Client
// Menggunakan LocalAuth agar sesi tersimpan dan tidak perlu scan QR tiap kali server restart
const client = new Client({
    authStrategy: new LocalAuth({ dataPath: './whatsapp-session' }),
    puppeteer: {
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-gpu'
        ],
    }
});

let lastQr = null;

// Event saat QR Code perlu dipindai
client.on('qr', (qr) => {
    console.log('=========================================');
    console.log('Scan QR Code di bawah ini menggunakan WhatsApp Anda:');
    qrcode.generate(qr, { small: true });
    console.log('=========================================');
    lastQr = qr;
});

// Event saat client berhasil terhubung dan siap
client.on('ready', () => {
    console.log('✅ WhatsApp Client is ready!');
    isClientReady = true;
    lastQr = null;
});

// Event saat client terputus
client.on('disconnected', (reason) => {
    console.log('❌ WhatsApp Client was disconnected', reason);
    isClientReady = false;
    lastQr = null;
});

// Mulai client
client.initialize();

// Endpoint untuk mengecek status
app.get('/', (req, res) => {
    res.json({
        status: 'success',
        message: 'WhatsApp Web API Gateway is running',
        isReady: isClientReady
    });
});

// Endpoint untuk status lengkap (digunakan di Laravel Admin Dashboard)
app.get('/status', (req, res) => {
    res.json({
        status: 'success',
        isReady: isClientReady,
        qr: lastQr ? `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(lastQr)}` : null
    });
});

// Endpoint untuk memutuskan koneksi WhatsApp
app.post('/disconnect', async (req, res) => {
    try {
        if (isClientReady) {
            await client.logout();
            isClientReady = false;
            lastQr = null;
            return res.json({
                status: 'success',
                message: 'WhatsApp disconnected successfully.'
            });
        }
        return res.status(400).json({
            status: 'error',
            message: 'WhatsApp Client is not connected.'
        });
    } catch (error) {
        console.error('Error disconnecting:', error);
        return res.status(500).json({
            status: 'error',
            message: 'Failed to disconnect WhatsApp Client.',
            error: error.message
        });
    }
});

// Endpoint untuk mengirim pesan
app.post('/send', async (req, res) => {
    if (!isClientReady) {
        return res.status(503).json({
            status: 'error',
            message: 'WhatsApp Client is not ready yet. Please scan QR Code or wait for connection.'
        });
    }

    try {
        const { target, message } = req.body;

        if (!target || !message) {
            return res.status(400).json({
                status: 'error',
                message: 'Target (phone number) and message are required.'
            });
        }

        // Bersihkan karakter non-digit dari target just in case
        let cleanTarget = target.replace(/\D/g, '');
        if (cleanTarget.startsWith('0')) {
            cleanTarget = '62' + cleanTarget.substring(1);
        }

        // Dapatkan ID resmi dari WhatsApp
        const registeredUser = await client.getNumberId(cleanTarget);
        
        if (!registeredUser) {
            return res.status(404).json({
                status: 'error',
                message: 'The phone number is not registered on WhatsApp.'
            });
        }

        const chatId = registeredUser._serialized;

        const response = await client.sendMessage(chatId, message);

        return res.status(200).json({
            status: 'success',
            message: 'Message sent successfully',
            response: response.id._serialized
        });

    } catch (error) {
        console.error('Error sending message:', error);
        return res.status(500).json({
            status: 'error',
            message: 'Failed to send message',
            error: error.message
        });
    }
});

app.listen(port, () => {
    console.log(`🚀 WhatsApp Web API Gateway running at http://localhost:${port}`);
    console.log(`Menunggu inisialisasi WhatsApp Client...`);
});
