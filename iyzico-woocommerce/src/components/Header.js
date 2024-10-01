import { useState } from '@wordpress/element';
import Logo from './Logo';
import { useLocalization, Localization } from './Localization';

const Header = ({ setActiveMenuItem, activeMenuItem }) => {
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)
    const isLocalizationLoaded = useLocalization()

    if (!isLocalizationLoaded) {
        return null;
    }

    const menuItems = [
        { name: Localization("header.menu.dashboard"), key: 'dashboard' },
        { name: Localization("header.menu.settings"), key: 'settings' }
    ]

    const handleMenuItemClick = (key) => {
        setActiveMenuItem(key)
        setIsMobileMenuOpen(false)
    }

    const getMenuItemClass = (key) => {
        const baseClass = "rounded-md px-3 py-2 text-sm font-medium"
        if (key === activeMenuItem) {
            return `${baseClass} bg-white text-iyzico-blue focus:outline-none focus:ring-0`
        }
        return `${baseClass} text-white hover:bg-white hover:text-iyzico-blue`
    }

    return (
        <header className="mb-4 shadow">
            <nav className="bg-iyzico-blue rounded-md">
                <div className="px-4 sm:px-6 lg:px-8">
                    <div className="relative flex h-16 items-center">
                        <div className="absolute inset-y-0 left-0 flex items-center sm:hidden">
                            <button type="button"
                                className="relative inline-flex items-center justify-start rounded-md p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                                onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}>
                            </button>
                        </div>
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <Logo />
                            </div>
                            <div className="hidden sm:ml-6 sm:block">
                                <div className="flex space-x-4">
                                    {menuItems.map((item) => (
                                        <button key={item.key} className={getMenuItemClass(item.key)} onClick={() => handleMenuItemClick(item.key)}>
                                            {item.name}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </header>
    )
}

export default Header