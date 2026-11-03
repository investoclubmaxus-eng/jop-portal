import Swal from 'sweetalert2';

// Reusable custom themed SweetAlert2 instance
const CustomSwal = Swal.mixin({
    background: '#0f172a', // slate-900
    color: '#f8fafc', // slate-50
    confirmButtonColor: '#4f46e5', // indigo-600
    cancelButtonColor: '#334155', // slate-700
    customClass: {
        popup: 'border border-slate-800 rounded-3xl shadow-2xl p-6 font-sans',
        title: 'text-lg font-bold text-white tracking-tight',
        htmlContainer: 'text-xs text-slate-300 leading-relaxed',
        confirmButton: 'px-5 py-2.5 rounded-xl text-xs font-semibold shadow-lg shadow-indigo-600/30 transition',
        cancelButton: 'px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 transition',
    },
});

// Toast notification (top-end)
export const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    background: '#1e293b', // slate-800
    color: '#f8fafc',
    customClass: {
        popup: 'border border-slate-700 rounded-2xl shadow-2xl text-xs py-2 px-3',
    },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

export const showSuccess = (title, text = '') => {
    return CustomSwal.fire({
        icon: 'success',
        title,
        text,
        confirmButtonText: 'Great, got it',
    });
};

export const showError = (title, text = '') => {
    return CustomSwal.fire({
        icon: 'error',
        title,
        text,
        confirmButtonColor: '#e11d48', // rose-600
        confirmButtonText: 'Dismiss',
    });
};

export const showWarning = (title, text = '') => {
    return CustomSwal.fire({
        icon: 'warning',
        title,
        text,
        confirmButtonColor: '#d97706', // amber-600
        confirmButtonText: 'Understood',
    });
};

export const showConfirm = async (title, text = '', confirmButtonText = 'Yes, Proceed', icon = 'question') => {
    const result = await CustomSwal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    });
    return result.isConfirmed;
};

export const showPrompt = async (title, inputPlaceholder = '', confirmButtonText = 'Submit') => {
    const result = await CustomSwal.fire({
        title,
        input: 'textarea',
        inputPlaceholder,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        inputAttributes: {
            class: 'w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans',
        },
    });
    return result;
};

export const showToast = (title, icon = 'success') => {
    return Toast.fire({
        icon,
        title,
    });
};

export default CustomSwal;
