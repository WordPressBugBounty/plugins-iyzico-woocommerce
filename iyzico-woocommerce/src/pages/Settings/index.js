import { useState, useEffect } from '@wordpress/element';
import IyzicoSettings from './IyzicoSettings';
import { useLocalization, Localization } from "../../components/Localization";
import { getSettings, saveSettings } from '../../helper/api_helper';

const Settings = () => {
    const [environment, setEnvironment] = useState(null);
    const [apiKey, setApiKey] = useState(null);
    const [secretKey, setSecretKey] = useState(null);
    const [title, setTitle] = useState(null);
    const [description, setDescription] = useState(null);
    const [formClass, setFormClass] = useState(null);
    const [paymentCheckoutValue, setPaymentCheckoutValue] = useState(null);
    const [orderStatus, setOrderStatus] = useState(null);
    const [overlayScript, setOverlayScript] = useState(null);
    const [formLanguage, setFormLanguage] = useState(null);
    const [affiliateNetwork, setAffiliateNetwork] = useState(null);
    const [enabled, setEnabled] = useState(null);
    const [pwiEnabled, setPwiEnabled] = useState(null);
    const [isLoading, setIsLoading] = useState(null);
    const [isSuccessSave, setIsSuccessSave] = useState(null);
    const [iyzicoWebhookUrlKey, setIyzicoWebhookUrlKey] = useState(null);
    const [errorMessage, setErrorMessage] = useState(null);

    useEffect(() => {
        const fetchOrders = async () => {
            setIsLoading(true);
            await getSettings().then((r) => {
                setEnvironment(r.checkout.api_type);
                setApiKey(r.checkout.api_key);
                setSecretKey(r.checkout.secret_key);
                setTitle(r.checkout.title);
                setDescription(r.checkout.description);
                setFormClass(r.checkout.form_class);
                setPaymentCheckoutValue(r.checkout.payment_checkout_value);
                setOrderStatus(r.checkout.order_status);
                setOverlayScript(r.checkout.overlay_script);
                setFormLanguage(r.checkout.form_language);
                setAffiliateNetwork(r.checkout.affiliate_network);
                setEnabled(r.checkout.enabled === "no" ? false : true);
                setPwiEnabled(r.pwi.enabled === "no" ? false : true);
                setIyzicoWebhookUrlKey(r.iyzicoWebhookUrlKey);
                setIsLoading(false);
            });
        };

        fetchOrders();
    }, []);

    const isLocalizationLoaded = useLocalization();

    if (!isLocalizationLoaded) {
        return null;
    }

    const handleSubmit = async (event) => {
        event.preventDefault();
        setErrorMessage(null);
        setIsSuccessSave(false);

        if ((enabled === true || pwiEnabled === true) && (!apiKey || !secretKey)) {
            setErrorMessage(Localization("settings.api_key_secret_key_required"));
            return;
        }

        const formData = new FormData();
        formData.append('api_type', environment);
        formData.append('api_key', apiKey);
        formData.append('secret_key', secretKey);
        formData.append('title', title);
        formData.append('description', description);
        formData.append('form_class', formClass);
        formData.append('payment_checkout_value', paymentCheckoutValue);
        formData.append('order_status', orderStatus);
        formData.append('overlay_script', overlayScript);
        formData.append('form_language', formLanguage);
        formData.append('affiliate_network', affiliateNetwork);
        formData.append('enabled', enabled === true ? 'yes' : 'no');
        formData.append('pwi_enabled', pwiEnabled === true ? 'yes' : 'no');

        saveSettings(formData).then((r) => {
            setIsSuccessSave(r.success);
        });
    };

    return (
        <form onSubmit={handleSubmit}>
            <div className="space-y-12">
                {isSuccessSave && (
                    <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <strong className="font-bold">{Localization("settings.success")}</strong>
                        <span className="block sm:inline ms-2">{Localization("settings.success_message")}</span>
                    </div>
                )}
                {errorMessage && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong className="font-bold">{Localization("settings.error")}</strong>
                        <span className="block sm:inline ms-2">{errorMessage}</span>
                    </div>
                )}
                {isLoading ? (
                    <p>{Localization("orders.loading")}</p>
                ) : (
                    <IyzicoSettings
                        iyzicoWebhookUrlKey={iyzicoWebhookUrlKey}
                        environment={environment}
                        setEnvironment={setEnvironment}
                        apiKey={apiKey}
                        setApiKey={setApiKey}
                        secretKey={secretKey}
                        setSecretKey={setSecretKey}
                        title={title}
                        setTitle={setTitle}
                        description={description}
                        setDescription={setDescription}
                        formClass={formClass}
                        setFormClass={setFormClass}
                        paymentCheckoutValue={paymentCheckoutValue}
                        setPaymentCheckoutValue={setPaymentCheckoutValue}
                        orderStatus={orderStatus}
                        setOrderStatus={setOrderStatus}
                        overlayScript={overlayScript}
                        setOverlayScript={setOverlayScript}
                        formLanguage={formLanguage}
                        setFormLanguage={setFormLanguage}
                        affiliateNetwork={affiliateNetwork}
                        setAffiliateNetwork={setAffiliateNetwork}
                        enabled={enabled}
                        setEnabled={setEnabled}
                        pwiEnabled={pwiEnabled}
                        setPwiEnabled={setPwiEnabled}
                    />
                )}
            </div>

            <div className="mt-6 flex items-center justify-end gap-x-6">
                <button
                    type="submit"
                    className="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    {Localization("buttons.save")}
                </button>
            </div>
        </form>
    );
};

export default Settings;