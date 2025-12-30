export interface Platform {
    id: number;
    name: string;
}

export interface Transaction {
    id: number;
    date: string;
    category: 'income' | 'expense';
    platform: Platform;
    type: string;
    description: string | null;
    amount: number;
    attachment: string | null;
    formatted_amount: string;
}
