export function formatIDR(value: string | number): string {
    const cents = toCents(String(value));
    const roundedRupiah = (cents < 0n ? cents - 50n : cents + 50n) / 100n;

    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(roundedRupiah);
}

export function moneyDifference(left: string, right: string): string {
    const cents = toCents(left) - toCents(right);
    const sign = cents < 0n ? '-' : '';
    const absolute = cents < 0n ? -cents : cents;

    return `${sign}${absolute / 100n}.${String(absolute % 100n).padStart(2, '0')}`;
}

function toCents(value: string): bigint {
    const trimmed = value.trim();
    const negative = trimmed.startsWith('-');
    const [whole = '0', fraction = ''] = trimmed.replace(/^[+-]/, '').split('.');
    const cents = BigInt(whole || '0') * 100n + BigInt(fraction.slice(0, 2).padEnd(2, '0') || '0');

    return negative ? -cents : cents;
}
