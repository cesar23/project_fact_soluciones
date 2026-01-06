import axios from 'axios';
export default {
    methods: {
        updateCurrentCompanyUser(companyId) {
            if (!companyId) {
                return;
            }
            axios
                .get(`update-current-company/${companyId}`, {
                    headers: window.headers_token,
                })
                .then((response) => {
                    console.log("🚀 ~ .then ~ response:", response);
                });
        },
    },
};
