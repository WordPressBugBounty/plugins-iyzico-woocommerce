import { useEffect, useState } from '@wordpress/element'
import { getLocalizations } from '../helper/api_helper'

let localizations = {}

export const useLocalization = () => {
    const [isLoaded, setIsLoaded] = useState(false)

    useEffect(() => {
        const fetchLocalizations = async () => {
            try {
                const data = await getLocalizations()
                localizations = data
                setIsLoaded(true)
            } catch (error) {
                console.error('Error fetching localizations:', error)
            }
        }

        if (Object.keys(localizations).length === 0) {
            fetchLocalizations()
        } else {
            setIsLoaded(true)
        }
    }, [])

    return isLoaded
}

export const Localization = (key) => {
    const keys = key.split('.')
    let current = localizations

    for (let k of keys) {
        if (current[k] === undefined) {
            console.warn(`Localization key not found: ${key}`)
            return key
        }
        current = current[k]
    }

    if (typeof current === 'string') {
        return current
    }

    console.warn(`Unexpected structure for key: ${key}`)
    return key
}