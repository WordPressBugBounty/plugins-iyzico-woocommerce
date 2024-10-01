import React from 'react';
import { SelectControl, TextControl, CheckboxControl } from '@wordpress/components';
import { useLocalization, Localization } from "../../components/Localization";

const IyzicoSettings = ({
    iyzicoWebhookUrlKey,
    environment,
    setEnvironment,
    apiKey,
    setApiKey,
    secretKey,
    setSecretKey,
    title,
    setTitle,
    description,
    setDescription,
    formClass,
    setFormClass,
    paymentCheckoutValue,
    setPaymentCheckoutValue,
    orderStatus,
    setOrderStatus,
    overlayScript,
    setOverlayScript,
    formLanguage,
    setFormLanguage,
    affiliateNetwork,
    setAffiliateNetwork,
    enabled,
    setEnabled,
    pwiEnabled,
    setPwiEnabled
}) => {
    const isLocalizationLoaded = useLocalization();

    if (!isLocalizationLoaded) {
        return null;
    }

    return (
        <div className="border-b border-gray-900/10 pb-12">
            <h2 className="text-base font-semibold leading-7 text-gray-900">
                {Localization("settings.title")}
            </h2>
            <p className="mt-1 text-sm leading-6 text-gray-600">
                {Localization("settings.description")}
            </p>
            <div className="settings-form mt-5 space-y-4">
                <TextControl
                    value={iyzicoWebhookUrlKey}
                    label={Localization("settings.fields.webhook.label")}
                    disabled
                />
                <SelectControl
                    label={Localization("settings.fields.environment.title")}
                    value={environment}
                    options={[
                        { label: Localization("settings.fields.environment.values.live"), value: 'https://api.iyzipay.com' },
                        { label: Localization("settings.fields.environment.values.sandbox"), value: 'https://sandbox-api.iyzipay.com' },
                    ]}
                    onChange={(value) => setEnvironment(value)}
                />
                <TextControl
                    value={apiKey}
                    label={Localization("settings.fields.api_key.title")}
                    onChange={(value) => setApiKey(value)}
                />
                <TextControl
                    value={secretKey}
                    label={Localization("settings.fields.secret_key.title")}
                    onChange={(value) => setSecretKey(value)}
                />
                <div className="flex flex-row space-x-4">
                    <CheckboxControl
                        label={Localization("settings.fields.enabled.title")}
                        checked={enabled}
                        onChange={(value) => setEnabled(value)}
                    />
                    <CheckboxControl
                        label={Localization("settings.fields.enabled.title_pwi")}
                        checked={pwiEnabled}
                        onChange={(value) => setPwiEnabled(value)}
                    />
                </div>
                <TextControl
                    value={title}
                    label={Localization("settings.fields.title.title")}
                    onChange={(value) => setTitle(value)}
                />
                <TextControl
                    value={description}
                    label={Localization("settings.fields.description.title")}
                    onChange={(value) => setDescription(value)}
                />
                <SelectControl
                    label={Localization("settings.fields.form_class.title")}
                    value={formClass}
                    options={[
                        { label: Localization("settings.fields.form_class.values.responsive"), value: 'responsive' },
                        { label: Localization("settings.fields.form_class.values.popup"), value: 'popup' },
                        { label: Localization("settings.fields.form_class.values.redirect"), value: 'redirect' },
                    ]}
                    onChange={(value) => setFormClass(value)}
                />
                <TextControl
                    value={paymentCheckoutValue}
                    label={Localization("settings.fields.payment_checkout_value.title")}
                    onChange={(value) => setPaymentCheckoutValue(value)}
                />
                <SelectControl
                    label={Localization("settings.fields.order_status.title")}
                    value={orderStatus}
                    options={[
                        { label: Localization("settings.fields.order_status.values.default"), value: 'default' },
                        { label: Localization("settings.fields.order_status.values.pending"), value: 'pending' },
                        { label: Localization("settings.fields.order_status.values.processing"), value: 'processing' },
                        { label: Localization("settings.fields.order_status.values.on-hold"), value: 'on-hold' },
                        { label: Localization("settings.fields.order_status.values.completed"), value: 'completed' },
                        { label: Localization("settings.fields.order_status.values.cancelled"), value: 'cancelled' },
                        { label: Localization("settings.fields.order_status.values.refunded"), value: 'refunded' },
                        { label: Localization("settings.fields.order_status.values.failed"), value: 'failed' },
                    ]}
                    onChange={(value) => setOrderStatus(value)}
                />
                <SelectControl
                    label={Localization("settings.fields.overlay_script.title")}
                    value={overlayScript}
                    options={[
                        { label: Localization("settings.fields.overlay_script.values.left"), value: 'bottomLeft' },
                        { label: Localization("settings.fields.overlay_script.values.right"), value: 'bottomRight' },
                        { label: Localization("settings.fields.overlay_script.values.hide"), value: 'hide' },
                    ]}
                    onChange={(value) => setOverlayScript(value)}
                />
                <SelectControl
                    label={Localization("settings.fields.form_language.title")}
                    value={formLanguage}
                    options={[
                        { label: Localization("settings.fields.form_language.values.automatic"), value: '' },
                        { label: Localization("settings.fields.form_language.values.turkish"), value: 'TR' },
                        { label: Localization("settings.fields.form_language.values.english"), value: 'EN' },
                    ]}
                    onChange={(value) => setFormLanguage(value)}
                />
                <TextControl
                    value={affiliateNetwork}
                    label={Localization("settings.fields.affiliate_network.title")}
                    onChange={(value) => setAffiliateNetwork(value)}
                    maxLength={14}
                />
            </div>
        </div>
    );
}

export default IyzicoSettings;