export const formatCurrency = (amount) => {
    if (typeof amount !== 'number') {
        console.error('Invalid amount:', amount);
        return '0,00 ₺';
    }
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}