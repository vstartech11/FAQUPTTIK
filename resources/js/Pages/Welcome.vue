<template>
    <div class="dashboard-container bg-gray-100 min-h-screen flex flex-col">
        <!-- Navbar Fixed -->
        <div
            class="navbar fixed top-0 left-0 w-full bg-gray-800 text-white shadow-md z-10"
        >
            <div
                class="container mx-auto flex justify-between items-center p-2"
            >
                <div class="flex items-center gap-2">
                    <img
                        src="../assets/images/logo-itk-retina-transformed.png"
                        alt="Logo ITK"
                        class="h-10 w-auto"
                    />
                    <h1 class="text-lg font-bold hidden md:block">UPT TIK</h1>
                </div>
                <div class="flex space-x-4">
                    <a
                        class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 rounded transition duration-300"
                        :href="route('login')"
                    >
                        Login
                    </a>
                    <span
                        class="text-gray-300 cursor-pointer hover:text-white"
                        @click="showAbout"
                    >
                        Tentang
                    </span>
                </div>
            </div>
        </div>

        <!-- Konten Dashboard -->
        <div class="mt-20 flex-grow">
            <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">
                Dashboard Monitoring
            </h1>
            <Chatbot />

            <!-- Grid untuk menampilkan setiap gedung -->
            <div class="grid grid-cols-2 gap-8 px-4">
                <div
                    v-for="gedung in gedungList"
                    :key="gedung.id"
                    class="chart-section bg-white rounded-lg shadow-lg p-6"
                >
                    <h2
                        class="text-2xl font-semibold text-gray-700 mb-4 text-center"
                    >
                        Lokasi Rak Server: {{ gedung.lokasiGedung }}
                    </h2>
                    <div class="chart-container mx-auto">
                        <canvas
                            :id="'chart-' + gedung.id"
                            class="chart-canvas"
                        ></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer bg-gray-800 text-white py-4 mt-8">
            <div class="container mx-auto text-center">
                <p>&copy; 2024 UPT TIK. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</template>

<script>
import {
    CategoryScale,
    Chart,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from "chart.js";
import Chatbot from "./Chatbot.vue";

Chart.register(
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
    Filler
);

export default {
    components: {
        Chatbot,
    },
    name: "DashboardMonitoring",
    data() {
        return {
            gedungList: [], // Daftar gedung
            charts: {}, // Menyimpan chart instances
            chartData: {}, // Menyimpan data terakhir untuk setiap gedung
            isFetching: false, // Status untuk melacak pengambilan data
        };
    },
    mounted() {
        this.fetchGedungList(); // Panggil sekali saat pertama kali dimuat
        this.updateInterval = setInterval(() => {
            if (!this.isFetching) {
                // Cek apakah data sedang diambil
                this.fetchGedungList();
            }
        }, 10000); // Update setiap 10 detik
    },
    methods: {
        async fetchGedungList() {
            if (this.isFetching) return; // Hindari pemanggilan jika sedang mengambil data

            this.isFetching = true; // Tandai bahwa data sedang diambil
            try {
                const response = await fetch("/data/gedung");

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();
                this.gedungList = data.data;

                for (const gedung of this.gedungList) {
                    await this.fetchAndRenderData(
                        gedung.id,
                        gedung.lokasiGedung
                    );
                }
            } catch (error) {
                console.error("Gagal mengambil data gedung:", error);
            } finally {
                this.isFetching = false; // Tandai bahwa data telah selesai diambil
            }
        },
        async fetchAndRenderData(idGedung, lokasiGedung) {
            try {
                const response = await fetch(`/data/gedung/${idGedung}`);

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();

                // Urutkan data berdasarkan updated_at
                const sortedData = data.data.sort(
                    (a, b) => new Date(a.updated_at) - new Date(b.updated_at)
                );

                // Proses data untuk chart
                const suhuRuang = sortedData.map((item) => item.suhu_ruang);
                const suhuRak = sortedData.map((item) => item.suhu_rak);
                const kelembapanRuang = sortedData.map(
                    (item) => item.kelembapan_ruang
                );
                const kelembapanRak = sortedData.map(
                    (item) => item.kelembapan_rak
                );
                const fan1 = sortedData.map((item) => item.fan_1);
                const fan2 = sortedData.map((item) => item.fan_2);
                const timestamps = sortedData.map((item) => {
                    const date = new Date(item.updated_at);
                    return date.toLocaleTimeString("en-GB", {
                        hour: "2-digit",
                        minute: "2-digit",
                    });
                });

                // Cek apakah ada perubahan data
                const newData = {
                    suhuRuang,
                    suhuRak,
                    kelembapanRuang,
                    kelembapanRak,
                    fan1,
                    fan2,
                    timestamps,
                };

                if (this.isDataChanged(idGedung, newData)) {
                    // Simpan data terbaru untuk gedung ini
                    this.chartData[idGedung] = newData;
                    // Render chart untuk gedung ini
                    this.renderChart(
                        `chart-${idGedung}`,
                        lokasiGedung,
                        timestamps,
                        suhuRuang,
                        suhuRak,
                        kelembapanRuang,
                        kelembapanRak,
                        fan1,
                        fan2
                    );
                }
            } catch (error) {
                console.error(
                    `Gagal mengambil data untuk gedung ${idGedung}:`,
                    error
                );
            }
        },

        // Fungsi untuk memeriksa apakah data berubah
        isDataChanged(idGedung, newData) {
            const lastData = this.chartData[idGedung];
            if (!lastData) return true;

            // Cek apakah ada perbedaan pada data
            return (
                !this.arraysEqual(lastData.suhuRuang, newData.suhuRuang) ||
                !this.arraysEqual(lastData.suhuRak, newData.suhuRak) ||
                !this.arraysEqual(
                    lastData.kelembapanRuang,
                    newData.kelembapanRuang
                ) ||
                !this.arraysEqual(
                    lastData.kelembapanRak,
                    newData.kelembapanRak
                ) ||
                !this.arraysEqual(lastData.fan1, newData.fan1) ||
                !this.arraysEqual(lastData.fan2, newData.fan2) ||
                !this.arraysEqual(lastData.timestamps, newData.timestamps)
            );
        },

        // Fungsi untuk memeriksa apakah dua array sama
        arraysEqual(arr1, arr2) {
            if (arr1.length !== arr2.length) return false;
            for (let i = 0; i < arr1.length; i++) {
                if (arr1[i] !== arr2[i]) return false;
            }
            return true;
        },

        renderChart(
            canvasId,
            lokasiGedung,
            timestamps,
            suhuRuang,
            suhuRak,
            kelembapanRuang,
            kelembapanRak,
            fan1,
            fan2
        ) {
            if (this.charts[canvasId]) {
                // Jangan buat chart baru jika chart sudah ada, cukup update
                this.charts[canvasId].data.labels = timestamps;
                this.charts[canvasId].data.datasets[0].data = suhuRuang;
                this.charts[canvasId].data.datasets[1].data = suhuRak;
                this.charts[canvasId].data.datasets[2].data = kelembapanRuang;
                this.charts[canvasId].data.datasets[3].data = kelembapanRak;
                this.charts[canvasId].data.datasets[4].data = fan1;
                this.charts[canvasId].data.datasets[5].data = fan2;
                this.charts[canvasId].update();
            } else {
                // Jika chart belum ada, buat chart baru
                const ctx = document.getElementById(canvasId).getContext("2d");
                this.charts[canvasId] = new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: timestamps,
                        datasets: [
                            {
                                label: "Suhu Ruang",
                                data: suhuRuang,
                                borderColor: "rgba(75, 192, 192, 1)",
                                backgroundColor: "rgba(75, 192, 192, 0.2)",
                                fill: true,
                                tension: 0.4,
                            },
                            {
                                label: "Suhu Rak",
                                data: suhuRak,
                                borderColor: "rgba(255, 99, 132, 1)",
                                backgroundColor: "rgba(255, 99, 132, 0.2)",
                                fill: true,
                                tension: 0.4,
                            },
                            {
                                label: "Kelembapan Ruang",
                                data: kelembapanRuang,
                                borderColor: "rgba(54, 162, 235, 1)",
                                backgroundColor: "rgba(54, 162, 235, 0.2)",
                                fill: true,
                                tension: 0.4,
                            },
                            {
                                label: "Kelembapan Rak",
                                data: kelembapanRak,
                                borderColor: "rgba(153, 102, 255, 1)",
                                backgroundColor: "rgba(153, 102, 255, 0.2)",
                                fill: true,
                                tension: 0.4,
                            },
                            {
                                label: "Fan 1",
                                data: fan1,
                                borderColor: "rgba(255, 206, 86, 1)",
                                backgroundColor: "rgba(255, 206, 86, 0.2)",
                                fill: true,
                                tension: 0.4,
                            },
                            {
                                label: "Fan 2",
                                data: fan2,
                                borderColor: "rgba(255, 159, 64, 1)",
                                backgroundColor: "rgba(255, 159, 64, 0.2)",
                                fill: true,
                                tension: 0.4,
                            },
                        ],
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: "Waktu",
                                },
                            },
                        },
                    },
                });
            }
        },

        // Methods for handling login and about
        showLogin() {
            this.$router.push("/login");
        },
        showAbout() {
            alert("Tentang aplikasi monitoring gedung.");
        },
    },
};
</script>

<style scoped>
.dashboard-container {
    background-color: #f7fafc;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.chart-section {
    background-color: white;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    flex: 1; /* Flex agar semua section sama besar */
    display: flex;
    flex-direction: column;
    justify-content: center; /* Pusatkan isi chart */
}

.chart-container {
    height: 400px;
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
}

.footer {
    background-color: #2d3748;
    color: white;
    text-align: center;
}

h2 {
    color: #2d3748;
    font-family: "Arial", sans-serif;
}

.navbar {
    background-color: #2d3748;
    color: white;
}

button {
    background-color: #3182ce;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #2b6cb0;
}

button,
.footer p {
    font-size: 14px;
}

/* Untuk memastikan chart berada di tengah pada setiap layar */
.chart-canvas {
    width: 100% !important; /* Pastikan chart mengisi lebar container */
    max-width: 800px; /* Atur maksimal lebar agar lebih responsif */
    margin: 0 auto; /* Pusatkan canvas */
}
</style>
