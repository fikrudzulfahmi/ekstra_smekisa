import Swal from 'sweetalert2';

// Kelas Tailwind yang disalin dari komponen tombol aplikasi agar tema seragam
const primaryButtonClass = 'inline-flex items-center justify-center rounded-full border border-transparent bg-[#0B1B36] px-6 py-2.5 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-[#122A52] focus:outline-none focus:ring-2 focus:ring-[#F2A93B] focus:ring-offset-2 active:bg-[#081428]';
const dangerButtonClass = 'inline-flex items-center justify-center rounded-full border border-transparent bg-red-600 px-6 py-2.5 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-800 mr-3';
const secondaryButtonClass = 'inline-flex items-center justify-center rounded-full border border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-[#1B2333] shadow-sm transition duration-150 ease-in-out hover:bg-[#F4F7FC] focus:outline-none focus:ring-2 focus:ring-[#3E6FD9]/40 focus:ring-offset-2';

/**
 * Menampilkan alert sederhana (pengganti alert() bawaan browser)
 */
export const showSuccessAlert = (message, title = 'Sukses!') => {
    return Swal.fire({
        title: title,
        text: message,
        icon: 'success',
        confirmButtonText: 'Tutup',
        customClass: {
            confirmButton: primaryButtonClass,
            popup: 'rounded-2xl shadow-xl border border-gray-100',
            title: 'text-[#0B1B36] font-bold'
        },
        buttonsStyling: false
    });
};

export const showErrorAlert = (message, title = 'Perhatian!') => {
    return Swal.fire({
        title: title,
        text: message,
        icon: 'error', // bisa warning atau error
        confirmButtonText: 'Mengerti',
        customClass: {
            confirmButton: primaryButtonClass,
            popup: 'rounded-2xl shadow-xl border border-gray-100',
            title: 'text-[#0B1B36] font-bold'
        },
        buttonsStyling: false
    });
};

export const showWarningAlert = (message, title = 'Perhatian!') => {
    return Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        confirmButtonText: 'Tutup',
        customClass: {
            confirmButton: primaryButtonClass,
            popup: 'rounded-2xl shadow-xl border border-gray-100',
            title: 'text-[#0B1B36] font-bold'
        },
        buttonsStyling: false
    });
};

/**
 * Menampilkan dialog konfirmasi (pengganti confirm() bawaan browser)
 */
export const showConfirm = (message, title = 'Konfirmasi Tindakan') => {
    return Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true, // Tombol batal di sebelah kiri
        customClass: {
            confirmButton: dangerButtonClass,
            cancelButton: secondaryButtonClass,
            popup: 'rounded-2xl shadow-xl border border-gray-100',
            title: 'text-[#0B1B36] font-bold'
        },
        buttonsStyling: false
    });
};
