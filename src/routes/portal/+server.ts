import { json } from '@sveltejs/kit';

export function GET() {
    return json({ apiUrl: process.env.TSP_API_URL || "http://localhost:8080" });
}
