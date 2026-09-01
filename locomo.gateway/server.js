const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();
const server = http.createServer(app);

// Konfigurasi CORS agar client dari localhost/domain lain bisa terhubung
const corsOptions = {
    origin: '*',
    methods: ['GET', 'POST'],
};

app.use(cors(corsOptions));
app.use(express.json());

// Inisialisasi Socket.io Server dengan CORS
const io = new Server(server, {
    cors: corsOptions,
    allowEIO3: true
});

// Listener Koneksi WebSocket
io.on('connection', (socket) => {
    console.log(`Klien terhubung: ${socket.id}`);

    // Klien (misal layar KDS/Kasir) bergabung ke room/channel spesifik (misal: caffe-kopi-kita)
    socket.on('join', (room) => {
        socket.join(room);
        console.log(`Klien ${socket.id} bergabung ke room: ${room}`);
    });

    socket.on('disconnect', () => {
        console.log(`Klien terputus: ${socket.id}`);
    });
});

// Endpoint HTTP POST untuk menerima trigger event dari Laravel
app.post('/emit', (req, res) => {
    const { channel, event, data } = req.body;

    if (!channel || !event || !data) {
        return res.status(400).json({
            success: false,
            message: 'Parameter "channel", "event", dan "data" wajib diisi.'
        });
    }

    console.log(`Menyiarkan event "${event}" ke channel/room "${channel}" dengan data:`, data);

    // Broadcast event ke room/channel Socket.io yang dituju
    io.to(channel).emit(event, data);

    return res.status(200).json({
        success: true,
        message: `Event "${event}" berhasil disiarkan ke channel "${channel}".`
    });
});

// Menjalankan server pada port 3000
const PORT = process.env.PORT || 3000;
server.listen(PORT, '0.0.0.0', () => {
    console.log(`Locomo Gateway berjalan di port ${PORT}`);
});
