import { ref } from 'vue';

export interface Toast {
    title: string;
    description?: string;
    variant?: 'default' | 'destructive';
}

const toasts = ref<Toast[]>([]);

export const useToast = () => {
    const toast = (toastData: Toast) => {
        // For now, we'll use a simple alert or console.log
        // In a real app, you'd probably want a proper toast system
        if (toastData.variant === 'destructive') {
            console.error(`${toastData.title}: ${toastData.description}`);
            alert(
                `Error: ${toastData.title}${toastData.description ? ' - ' + toastData.description : ''}`,
            );
        } else {
            console.log(`${toastData.title}: ${toastData.description}`);
            // You could show a success notification here
        }
    };

    return {
        toast,
        toasts,
    };
};
