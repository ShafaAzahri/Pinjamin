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
            '--disable-setuid-sandbox'
        ],
    },
    webVersionCache: {
        type: 'remote',
        remotePath: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.2412.54.html',
    }
});

// Event saat QR Code perlu dipindai
client.on('qr', (qr) => {
    console.log('=========================================');
    console.log('Scan QR Code di bawah ini menggunakan WhatsApp Anda:');
    qrcode.generate(qr, { small: true });
    console.log('=========================================');
});

// Event saat client berhasil terhubung dan siap
client.on('ready', () => {
    console.log('✅ WhatsApp Client is ready!');
    isClientReady = true;
});

// Event saat client terputus
client.on('disconnected', (reason) => {
    console.log('❌ WhatsApp Client was disconnected', reason);
    isClientReady = false;
    // Client otomatis mencoba reconnect atau Anda bisa destroy dan init ulang
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

        // whatsapp-web.js membutuhkan format 'nomor@c.us'
        // 'target' dari Laravel sudah diformat ke '628xxx'
        const chatId = `${target}@c.us`;

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
