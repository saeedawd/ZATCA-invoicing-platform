<?php

namespace App\Support;

class Labels
{
    public static function partyType(?string $value): string
    {
        return match ($value) {
            'customer' => 'عميل',
            'supplier' => 'مورد',
            'both' => 'عميل ومورد',
            default => $value ?: '—',
        };
    }

    public static function invoiceType(?string $value): string
    {
        return match ($value) {
            'simplified' => 'مبسطة',
            'standard' => 'ضريبية',
            default => $value ?: '—',
        };
    }

    public static function direction(?string $value): string
    {
        return match ($value) {
            'sales' => 'مبيعات',
            'purchase' => 'مشتريات',
            default => $value ?: '—',
        };
    }

    public static function documentType(?string $value): string
    {
        return match ((string) $value) {
            '388' => 'فاتورة',
            '381' => 'إشعار دائن',
            '383' => 'إشعار مدين',
            default => $value ?: '—',
        };
    }

    public static function submissionType(?string $value): string
    {
        return match ($value) {
            'reporting' => 'تبليغ',
            'clearance' => 'اعتماد',
            default => $value ?: '—',
        };
    }

    public static function taxCategory(?string $value): string
    {
        return match ($value) {
            'S' => 'خاضع',
            'Z' => 'صفرية',
            'E' => 'معفى',
            'O' => 'خارج النطاق',
            default => $value ?: '—',
        };
    }

    public static function auditAction(?string $value): string
    {
        return match ($value) {
            'invoice.created' => 'إنشاء فاتورة',
            'invoice.updated' => 'تعديل فاتورة',
            'invoice.issued' => 'إصدار فاتورة',
            default => $value ?: '—',
        };
    }

    public static function paymentMethod(?string $value): string
    {
        return match ($value) {
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل بنكي',
            'card' => 'بطاقة',
            'cheque' => 'شيك',
            default => $value ?: '—',
        };
    }

    public static function paymentStatus(?string $value): string
    {
        return match ($value) {
            'unpaid' => 'غير مدفوعة',
            'partial' => 'مدفوعة جزئياً',
            'paid' => 'مدفوعة',
            default => $value ?: '—',
        };
    }

    public static function quoteStatus(?string $value): string
    {
        return match ($value) {
            'draft' => 'مسودة',
            'sent' => 'مُرسل',
            'accepted' => 'مقبول',
            'rejected' => 'مرفوض',
            'converted' => 'محوّل لفاتورة',
            'expired' => 'منتهي',
            default => $value ?: '—',
        };
    }

    public static function userRole(?string $value): string
    {
        return match ($value) {
            'owner' => 'مالك الحساب',
            'admin' => 'مدير',
            'accountant' => 'محاسب',
            'viewer' => 'مشاهد',
            default => $value ?: '—',
        };
    }

    public static function resolve(string $type, ?string $value): string
    {
        return match ($type) {
            'partyType' => self::partyType($value),
            'invoiceType' => self::invoiceType($value),
            'direction' => self::direction($value),
            'documentType' => self::documentType($value),
            'submissionType' => self::submissionType($value),
            'taxCategory' => self::taxCategory($value),
            'auditAction' => self::auditAction($value),
            'paymentMethod' => self::paymentMethod($value),
            'paymentStatus' => self::paymentStatus($value),
            'quoteStatus' => self::quoteStatus($value),
            'userRole' => self::userRole($value),
            default => $value ?: '—',
        };
    }
}
