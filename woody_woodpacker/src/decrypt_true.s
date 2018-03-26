section .text
global _decrypt_true
;RDI,   RSI,   RDX, RCX, R8, R9, XMM0–7
;data, offset, size, key, index

_start:
    mov r13, 0x33333333
	lea rdi, [0x22222222]
    mov bl, 1

looping:
    sub BYTE [rdi], bl

increment:
    inc rdi
    add rsi, 1
    cmp rsi, r13 
    jne looping
    jmp 0x11111111