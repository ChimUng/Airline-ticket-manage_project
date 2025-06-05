from fastapi import FastAPI
from pydantic import BaseModel
from fastapi.middleware.cors import CORSMiddleware
from rag_chain import load_rag_chain

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

class Message(BaseModel):
    message: str

qa = load_rag_chain()

@app.post("/chat")
async def chat(msg: Message):
    result = qa.run(msg.message)
    return {"reply": result}
