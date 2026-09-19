<?php

namespace App\Domains\Zatca\Services;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Organization\Models\Organization;
use DOMDocument;
use DOMElement;

class ZatcaXmlBuilder
{
    public function build(Invoice $invoice, Organization $organization, string $previousHash, int $counter): string
    {
        $invoice->loadMissing(['lines', 'taxTotals', 'party', 'references.referencedInvoice']);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $invoiceNode = $dom->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $invoiceNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $dom->appendChild($invoiceNode);

        $this->append($dom, $invoiceNode, 'cbc:ProfileID', 'reporting:1.0');
        $this->append($dom, $invoiceNode, 'cbc:ID', $invoice->invoice_number);
        $this->append($dom, $invoiceNode, 'cbc:UUID', $invoice->uuid_zatca);
        $this->append($dom, $invoiceNode, 'cbc:IssueDate', $invoice->issue_date?->format('Y-m-d'));
        $this->append($dom, $invoiceNode, 'cbc:IssueTime', $invoice->issue_time ?: '00:00:00');
        $this->append($dom, $invoiceNode, 'cbc:InvoiceTypeCode', $invoice->document_type_code, [
            'name' => $invoice->isSimplified() ? '0200000' : '0100000',
        ]);
        $this->append($dom, $invoiceNode, 'cbc:DocumentCurrencyCode', $invoice->currency ?: 'SAR');
        $this->append($dom, $invoiceNode, 'cbc:TaxCurrencyCode', $invoice->currency ?: 'SAR');

        $billingRef = $invoice->references->first();
        if ($billingRef?->referencedInvoice) {
            $billingReference = $dom->createElement('cac:BillingReference');
            $invoiceDocRef = $dom->createElement('cac:InvoiceDocumentReference');
            $this->append($dom, $invoiceDocRef, 'cbc:ID', $billingRef->referencedInvoice->invoice_number);
            $billingReference->appendChild($invoiceDocRef);
            $invoiceNode->appendChild($billingReference);
        }

        $additionalDoc = $dom->createElement('cac:AdditionalDocumentReference');
        $this->append($dom, $additionalDoc, 'cbc:ID', 'ICV');
        $uuidNode = $dom->createElement('cac:UUID');
        // placeholder structure for counter
        $this->append($dom, $additionalDoc, 'cbc:UUID', (string) $counter);
        $invoiceNode->appendChild($additionalDoc);

        $pihDoc = $dom->createElement('cac:AdditionalDocumentReference');
        $this->append($dom, $pihDoc, 'cbc:ID', 'PIH');
        $attachment = $dom->createElement('cac:Attachment');
        $binary = $dom->createElement('cbc:EmbeddedDocumentBinaryObject', $previousHash);
        $binary->setAttribute('mimeCode', 'text/plain');
        $attachment->appendChild($binary);
        $pihDoc->appendChild($attachment);
        $invoiceNode->appendChild($pihDoc);

        $supplier = $dom->createElement('cac:AccountingSupplierParty');
        $supplier->appendChild($this->partyNode($dom, $organization->legal_name_ar, $organization->vat_number, [
            'building' => $organization->building_number,
            'street' => $organization->street,
            'district' => $organization->district,
            'city' => $organization->city,
            'postal' => $organization->postal_code,
            'country' => $organization->country ?: 'SA',
            'cr' => $organization->cr_number,
        ]));
        $invoiceNode->appendChild($supplier);

        $customer = $dom->createElement('cac:AccountingCustomerParty');
        if ($invoice->party) {
            $customer->appendChild($this->partyNode($dom, $invoice->party->name, $invoice->party->vat_number, [
                'building' => $invoice->party->building_number,
                'street' => $invoice->party->street,
                'district' => $invoice->party->district,
                'city' => $invoice->party->city,
                'postal' => $invoice->party->postal_code,
                'country' => $invoice->party->country ?: 'SA',
                'cr' => $invoice->party->cr_number,
            ]));
        } else {
            $customerParty = $dom->createElement('cac:Party');
            $customer->appendChild($customerParty);
        }
        $invoiceNode->appendChild($customer);

        $payment = $dom->createElement('cac:PaymentMeans');
        $this->append($dom, $payment, 'cbc:PaymentMeansCode', $invoice->payment_means_code ?: '10');
        $invoiceNode->appendChild($payment);

        $taxTotal = $dom->createElement('cac:TaxTotal');
        $this->append($dom, $taxTotal, 'cbc:TaxAmount', number_format((float) $invoice->tax_amount, 2, '.', ''), [
            'currencyID' => 'SAR',
        ]);
        foreach ($invoice->taxTotals as $bucket) {
            $subtotal = $dom->createElement('cac:TaxSubtotal');
            $this->append($dom, $subtotal, 'cbc:TaxableAmount', number_format((float) $bucket->taxable_amount, 2, '.', ''), ['currencyID' => 'SAR']);
            $this->append($dom, $subtotal, 'cbc:TaxAmount', number_format((float) $bucket->tax_amount, 2, '.', ''), ['currencyID' => 'SAR']);
            $category = $dom->createElement('cac:TaxCategory');
            $this->append($dom, $category, 'cbc:ID', $bucket->tax_category);
            $this->append($dom, $category, 'cbc:Percent', number_format((float) $bucket->tax_rate, 2, '.', ''));
            $scheme = $dom->createElement('cac:TaxScheme');
            $this->append($dom, $scheme, 'cbc:ID', 'VAT');
            $category->appendChild($scheme);
            $subtotal->appendChild($category);
            $taxTotal->appendChild($subtotal);
        }
        $invoiceNode->appendChild($taxTotal);

        $legal = $dom->createElement('cac:LegalMonetaryTotal');
        $this->append($dom, $legal, 'cbc:LineExtensionAmount', number_format((float) $invoice->line_extension_amount, 2, '.', ''), ['currencyID' => 'SAR']);
        $this->append($dom, $legal, 'cbc:TaxExclusiveAmount', number_format((float) $invoice->taxable_amount, 2, '.', ''), ['currencyID' => 'SAR']);
        $this->append($dom, $legal, 'cbc:TaxInclusiveAmount', number_format((float) $invoice->total_amount, 2, '.', ''), ['currencyID' => 'SAR']);
        $this->append($dom, $legal, 'cbc:PayableAmount', number_format((float) $invoice->payable_amount, 2, '.', ''), ['currencyID' => 'SAR']);
        $invoiceNode->appendChild($legal);

        foreach ($invoice->lines as $line) {
            $lineNode = $dom->createElement('cac:InvoiceLine');
            $this->append($dom, $lineNode, 'cbc:ID', (string) $line->line_no);
            $this->append($dom, $lineNode, 'cbc:InvoicedQuantity', number_format((float) $line->quantity, 4, '.', ''), [
                'unitCode' => $line->unit_code ?: 'PCE',
            ]);
            $this->append($dom, $lineNode, 'cbc:LineExtensionAmount', number_format((float) $line->line_net, 2, '.', ''), ['currencyID' => 'SAR']);

            $item = $dom->createElement('cac:Item');
            $this->append($dom, $item, 'cbc:Name', $line->description);
            $classified = $dom->createElement('cac:ClassifiedTaxCategory');
            $this->append($dom, $classified, 'cbc:ID', $line->tax_category);
            $this->append($dom, $classified, 'cbc:Percent', number_format((float) $line->tax_rate, 2, '.', ''));
            $scheme = $dom->createElement('cac:TaxScheme');
            $this->append($dom, $scheme, 'cbc:ID', 'VAT');
            $classified->appendChild($scheme);
            $item->appendChild($classified);
            $lineNode->appendChild($item);

            $price = $dom->createElement('cac:Price');
            $this->append($dom, $price, 'cbc:PriceAmount', number_format((float) $line->unit_price, 4, '.', ''), ['currencyID' => 'SAR']);
            $lineNode->appendChild($price);

            $invoiceNode->appendChild($lineNode);
        }

        // Remove unused variable warning path
        unset($uuidNode);

        return $dom->saveXML() ?: '';
    }

    /**
     * @param  array<string, string|null>  $address
     */
    protected function partyNode(DOMDocument $dom, ?string $name, ?string $vat, array $address): DOMElement
    {
        $party = $dom->createElement('cac:Party');

        if ($vat) {
            $taxScheme = $dom->createElement('cac:PartyTaxScheme');
            $this->append($dom, $taxScheme, 'cbc:CompanyID', $vat);
            $scheme = $dom->createElement('cac:TaxScheme');
            $this->append($dom, $scheme, 'cbc:ID', 'VAT');
            $taxScheme->appendChild($scheme);
            $party->appendChild($taxScheme);
        }

        if (! empty($address['cr'])) {
            $identification = $dom->createElement('cac:PartyIdentification');
            $id = $dom->createElement('cbc:ID', $address['cr']);
            $id->setAttribute('schemeID', 'CRN');
            $identification->appendChild($id);
            $party->appendChild($identification);
        }

        $postalAddress = $dom->createElement('cac:PostalAddress');
        $this->append($dom, $postalAddress, 'cbc:StreetName', $address['street'] ?? '');
        $this->append($dom, $postalAddress, 'cbc:BuildingNumber', $address['building'] ?? '');
        $this->append($dom, $postalAddress, 'cbc:CitySubdivisionName', $address['district'] ?? '');
        $this->append($dom, $postalAddress, 'cbc:CityName', $address['city'] ?? '');
        $this->append($dom, $postalAddress, 'cbc:PostalZone', $address['postal'] ?? '');
        $country = $dom->createElement('cac:Country');
        $this->append($dom, $country, 'cbc:IdentificationCode', $address['country'] ?? 'SA');
        $postalAddress->appendChild($country);
        $party->appendChild($postalAddress);

        $legal = $dom->createElement('cac:PartyLegalEntity');
        $this->append($dom, $legal, 'cbc:RegistrationName', $name ?? '');
        $party->appendChild($legal);

        return $party;
    }

    /**
     * @param  array<string, string>  $attributes
     */
    protected function append(DOMDocument $dom, DOMElement $parent, string $name, ?string $value, array $attributes = []): DOMElement
    {
        $node = $dom->createElement($name, htmlspecialchars((string) $value, ENT_XML1));
        foreach ($attributes as $key => $attributeValue) {
            $node->setAttribute($key, $attributeValue);
        }
        $parent->appendChild($node);

        return $node;
    }
}
