section .text
global _decrypt_true
extern _ft_putnbr
extern _ft_putchar
;RDI,   RSI,   RDX, RCX, R8, R9, XMM0–7
;data, offset, size, key, index

_decrypt_true:
    ;enter 16, 0
    mov r13, rdx
	lea rdi, [rdi + rsi]
    mov bl, 1

;loop:
;    cmp BYTE [rdi], 1
;    jb increment
;
looping:
;    cmp BYTE[rdi], 255
;    jae increment
    sub BYTE [rdi], bl

increment:
    ;lea rdi, [rdi + 1]
    inc rdi
    add rsi, 1
    cmp rsi, r13 
    jne looping

return:
    ;leave
    ret
    ;jmp 0x11111111