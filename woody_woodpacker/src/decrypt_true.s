section .text
global _decrypt_true
extern _ft_putnbr
extern _ft_putchar

_decrypt_true:
    ;push rbp
    ;mov rbp, rsp
    mov rax, 0
  test5:
    ;rsi == 12
    mov rdi, rax
    push rax
    call _ft_putnbr
    pop rax
    cmp rax, 12
	je end
	inc rax
	jmp test5
	;dec rax

end:
    mov rdi, rax
    call _ft_putnbr
    ret