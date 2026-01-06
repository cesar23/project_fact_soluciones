export const advance = {   
    methods: {
        async getAdvance(personId) {
            const response = await this.$http(`/advances/get-advance/${personId}`);
            console.log("🚀 ~ getAdvance ~ response:", response)
        },
    }
}