import Content from "./components/Content"
import Header from "./components/Header"
import { useState } from '@wordpress/element'

const App = () => {
    const [activeMenuItem, setActiveMenuItem] = useState('dashboard')

    return (
        <>
            <Header setActiveMenuItem={setActiveMenuItem} activeMenuItem={activeMenuItem} />
            <div className="min-h-screen flex flex-col">
                <Content activeMenuItem={activeMenuItem} />
            </div>
        </>
    );
}

export default App