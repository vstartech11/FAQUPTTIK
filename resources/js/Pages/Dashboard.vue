<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, watch } from "vue";

const gedungList = ref([]);
const selectedGedung = ref(null);
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

const isIpAddressValid = ref(true);

// Fetch data gedung dari server
const fetchGedungData = async () => {
    try {
        const response = await fetch("/data/gedung");
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        const result = await response.json();
        gedungList.value = result.data;
    } catch (error) {
        console.error("Gagal mengambil data gedung:", error);
    }
};

onMounted(() => {
    fetchGedungData();
});

// Fungsi untuk mengedit gedung
const editGedung = (gedung) => {
    selectedGedung.value = { ...gedung };
};

// Fungsi untuk menyimpan perubahan pada gedung (Add atau Edit)
const saveGedung = async () => {
    if (selectedGedung.value && isIpAddressValid.value) {
        const method = selectedGedung.value.id ? "PUT" : "POST";
        const url = selectedGedung.value.id
            ? `/gedung/${selectedGedung.value.id}`
            : "/gedung";

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    lokasiGedung: selectedGedung.value.lokasiGedung,
                    ipAddress: selectedGedung.value.ipAddress,
                }),
            });

            if (!response.ok) {
                throw new Error(
                    `Gagal menyimpan data! Status: ${response.status}`
                );
            }

            const result = await response.json();
            alert(
                `Data ${result.data.lokasiGedung} berhasil di${
                    method === "POST" ? "tambah" : "update"
                }.`
            );
            fetchGedungData();
            selectedGedung.value = null; // Reset form setelah berhasil simpan
        } catch (error) {
            console.error("Error menyimpan data:", error);
        }
    } else {
        alert("Alamat IP tidak valid.");
    }
};

// Fungsi untuk menghapus gedung
const deleteGedung = async (id) => {
    if (confirm(`Yakin ingin menghapus gedung dengan ID: ${id}?`)) {
        try {
            const response = await fetch(`/gedung/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Content-Type": "application/json",
                },
            });

            if (!response.ok) {
                throw new Error(
                    `Gagal menghapus data! Status: ${response.status}`
                );
            }

            alert("Data berhasil dihapus.");
            fetchGedungData();
        } catch (error) {
            console.error("Error menghapus data:", error);
        }
    }
};

// Watcher untuk memvalidasi IP Address
watch(
    () => selectedGedung.value?.ipAddress,
    (newIpAddress) => {
        const ipPattern =
            /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        isIpAddressValid.value = ipPattern.test(newIpAddress);
    }
);

// Fungsi untuk menambahkan gedung
const addGedung = () => {
    selectedGedung.value = { lokasiGedung: "", ipAddress: "" }; // Memulai form kosong
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold mb-4">
                            Data Gedung dan IP Address
                        </h3>

                        <!-- Button Add Gedung -->
                        <button
                            @click="addGedung"
                            class="px-4 py-2 mb-4 bg-green-600 text-white rounded hover:bg-green-700 transition duration-300"
                        >
                            Add Gedung
                        </button>

                        <table
                            class="min-w-full border border-gray-200 divide-y divide-gray-200"
                        >
                            <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase"
                                    >
                                        No
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase"
                                    >
                                        Nama Gedung
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase"
                                    >
                                        IP Address
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-sm font-medium text-gray-600 uppercase"
                                    >
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr
                                    v-for="(gedung, index) in gedungList"
                                    :key="gedung.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        {{ gedung.lokasiGedung }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        {{ gedung.ipAddress }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <button
                                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition duration-300 mr-2"
                                            @click="editGedung(gedung)"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition duration-300"
                                            @click="deleteGedung(gedung.id)"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="gedungList.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-6 py-4 text-center text-sm text-gray-500"
                                    >
                                        Tidak ada data gedung yang tersedia.
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Modal untuk Edit atau Add -->
                        <div
                            v-if="selectedGedung"
                            class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50"
                        >
                            <div
                                class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full"
                            >
                                <h3 class="text-xl font-semibold mb-4">
                                    {{
                                        selectedGedung.id
                                            ? "Edit Gedung"
                                            : "Add Gedung"
                                    }}
                                </h3>
                                <div class="mb-4">
                                    <label
                                        for="lokasiGedung"
                                        class="block text-sm font-medium text-gray-600"
                                        >Nama Gedung</label
                                    >
                                    <input
                                        v-model="selectedGedung.lokasiGedung"
                                        type="text"
                                        id="lokasiGedung"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                        required
                                    />
                                </div>
                                <div class="mb-4">
                                    <label
                                        for="ipAddress"
                                        class="block text-sm font-medium text-gray-600"
                                        >IP Address</label
                                    >
                                    <input
                                        v-model="selectedGedung.ipAddress"
                                        type="text"
                                        id="ipAddress"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                        :class="{
                                            'border-red-500': !isIpAddressValid,
                                        }"
                                        required
                                    />
                                    <p
                                        v-if="!isIpAddressValid"
                                        class="text-red-500 text-sm mt-1"
                                    >
                                        Alamat IP tidak valid.
                                    </p>
                                </div>
                                <div class="flex justify-end">
                                    <button
                                        @click="saveGedung"
                                        class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300"
                                    >
                                        Save
                                    </button>
                                    <button
                                        @click="selectedGedung = null"
                                        class="ml-2 py-2 px-4 border border-gray-300 rounded-md text-gray-700"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
