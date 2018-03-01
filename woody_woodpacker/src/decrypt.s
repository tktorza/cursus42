global _ft_decrypt
extern _strtol
; 1er argument: le ptr sur le premier octect de la zone .text a decrypter
; 2eme argument: la taille totale de la zone .text
; 3eme argument: la clé entiere 
; 4eme argument: je garde à partir de cet int jusqu'a la fin la clé

section .text
_ft_decrypt:
	push rbp
	mov rbp, rsp
    cmp rdi, 0
	jle end
    cmp rsi, 0
	jle end
    mov r9, rdi
    mov r10, rsi
    mov r11, rdx
    mov r12, rcx

key_off:
    cmp r12, 0
    jle key
    dec r12
    inc r11
    jmp key_off

key:
    mov rdi, r11
    mov rsi, 0
    mov rdx, 0
    call _strtol
    mov r12, rax ; la clé en int

while:
    cmp r10, 0
    jle end
    mov rax, byte[r9]
    idiv 2
    sub rax, r12
    inc r9 
    dec r10
    jmp while

end:
	leave
	ret