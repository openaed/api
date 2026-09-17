import './bootstrap';

window.firstValid = (values) => {
    for (const value of values) {
        if (value !== null && value !== undefined) {
            return value;
        }
    }
    return null;
}